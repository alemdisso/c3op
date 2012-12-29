<?php

class Projects_ReceivableController extends Zend_Controller_Action
{
    private $receivableMapper;
    private $projectMapper;
    private $db;

    public function preDispatch()
    {
        try {
            $checker = new C3op_Access_PrivilegeChecker();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function init()
    {
        $this->db = Zend_Registry::get('db');
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_ReceivableCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/receivable/success/?id=' . $id);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();
            $projectId = $data['project'];
            $this->populateProjectFields($projectId, $form);
        }
    }

    public function detailAction()
    {
        $pageData = array();

        $actionsList = array();
        $this->initActionMapper();
        $this->initReceivableMapper();
        $this->initProjectMapper();
        $this->initContactMapper();
        $this->initTeamMemberMapper();

        $receivableToBeDetailed =  $this->initReceivableWithCheckedId($this->receivableMapper);
        $projectToBeDetailed = $this->projectMapper->findById($receivableToBeDetailed->getProject());

        //  actionHeader
        //    id
        //    title
        //    projectId
        //    projectTitle
        //    description
        //    predictedDate
        //    predictedValue
        //    realDate
        //    realValue
        //    productsRequired
        //      * id =>
        //        actionTitle
        //        responsibleName
        //        status
        //

        $requiredProducts = $this->receivableMapper->getAllProducts($receivableToBeDetailed);
        $requiredProductsData = array();
        $statusTypes = new C3op_Projects_ActionStatusTypes();

        foreach($requiredProducts as $loopActionId) {

            $loopAction = $this->actionMapper->findById($loopActionId);
            $data = array();
            $data['title'] = $loopAction->getTitle();

            if ($loopAction->getResponsible()) {
                $theContact = $this->contactMapper->findById($loopAction->getResponsible());
                $data['responsibleName'] = $theContact->getName();
            } else {
                $data['responsibleName'] = $this->view->translate("#Not defined");
            }

            $data['status'] = $statusTypes->TitleForType($loopAction->getStatus());

            $requiredProductsData[$loopActionId] = $data;


        }

        $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($receivableToBeDetailed->getPredictedDate());

        $validator = new C3op_Util_ValidDate();
        if ($validator->isValid($receivableToBeDetailed->getRealDate())) {
            $realDate = C3op_Util_DateDisplay::FormatDateToShow($receivableToBeDetailed->getRealDate());
        } else {
            $realDate = $this->view->translate("#(not received)");
        }


        $currencyDisplay = new  C3op_Util_CurrencyDisplay();
        $predictedValue = $currencyDisplay->FormatCurrency($receivableToBeDetailed->getPredictedValue());
        if ($receivableToBeDetailed->getRealValue() > 0) {
            $realValue = $currencyDisplay->FormatCurrency($receivableToBeDetailed->getRealValue());
        } else {
            $realValue = $this->view->translate("#(not received)");
        }

        if ($receivableToBeDetailed->getDescription() != "") {
            $description = nl2br($receivableToBeDetailed->getDescription());
        } else {
            $description = $this->view->translate("#(not registered)");
        }


        $receivableData = array(
            'id'               => $receivableToBeDetailed->getId(),
            'projectId'        => $projectToBeDetailed->getId(),
            'projectTitle'     => $projectToBeDetailed->getShortTitle(),
            'title'            => $receivableToBeDetailed->getTitle(),
            'description'      => $description,
            'predictedDate'    => $predictedDate,
            'predictedValue'   => $predictedValue,
            'realValue'        => $realValue,
            'realDate'         => $realDate,
            'requiredProducts' => $requiredProductsData,
        );



        $pageData = array(
            'receivableData' => $receivableData,
        );

        $this->view->pageData = $pageData;

    }




    public function editAction()
    {
        $form = new C3op_Form_ReceivableEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/receivable/success/?id=' . $id);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();
            $filters = array(
                'id' => new Zend_Filter_Alnum(),
            );
            $validators = array(
                'id' => new C3op_Util_ValidId(),
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);
            if ($input->isValid()) {
                $id = $input->id;
                if (!isset($this->receivableMapper)) {
                    $this->receivableMapper = new C3op_Projects_ReceivableMapper($this->db);
                }
                $thisReceivable = $this->receivableMapper->findById($id);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'title', $thisReceivable->GetTitle());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
                $this->SetDateValueToFormField($form, 'predictedDate', $thisReceivable->GetPredictedDate());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'predictedValue', $thisReceivable->GetPredictedValue());
//                $this->SetDateValueToFormField($form, 'realDate', $thisReceivable->GetRealDate());
//                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'realValue', $thisReceivable->GetRealValue());
                $projectId = $this->populateProjectFields($thisReceivable->GetProject(), $form);
            }

        }
    }

    public function successAction()
    {
        $this->initReceivableMapper();
        $receivableRelated =  $this->initReceivableWithCheckedId($this->receivableMapper);

        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
            $this->getResponse()->setHeader('Refresh', '1; URL=/projects/receivable/detail/?id=' . $receivableRelated->getId());
        } else {
            $this->_redirect('/projects');
        }
    }

    public function notifyAction()
    {
        // cria form
        $form = new C3op_Form_ReceivableNotify;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/receivable/success/?id=' . $id);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();
            $filters = array(
                'id' => new Zend_Filter_Alnum(),
            );
            $validators = array(
                'id' => new C3op_Util_ValidId(),
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);

            if ($input->isValid()) {
                $this->initReceivableMapper();
                $receivableToBeNotified =  $this->initReceivableWithCheckedId($this->receivableMapper);
                $this->view->title = $receivableToBeNotified->GetTitle();
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $receivableToBeNotified->getId());
//                $this->SetDateValueToFormField($form, 'realDate', $thisReceivable->GetRealDate());
//                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'realValue', $thisReceivable->GetRealValue());
                $projectId = $this->populateProjectFields($receivableToBeNotified->GetProject(), $form);
            }

        }
    }

    private function populateProjectFields($projectId, Zend_Form $form)
    {
        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $projectField = $form->getElement('project');
            $projectField->setValue($projectId);
            $this->initProjectMapper();
            $thisProject = $this->projectMapper->findById($projectId);
            $this->view->projectTitle = $thisProject->GetShortTitle();
            $this->view->projectId = $projectId;
            return $projectId;
        } else throw new C3op_Projects_ReceivableException("Receivable needs a positive integer project id.");

    }

    private function initProjectMapper()
    {
        if (!isset($this->projectMapper)) {
            $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }
    }

    private function initActionMapper()
    {
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }
    }

    private function initTeamMemberMapper()
    {
        if (!isset($this->teamMemberMapper)) {
            $this->teamMemberMapper = new C3op_Projects_TeamMemberMapper($this->db);
        }
    }

    private function initContactMapper()
    {
        if (!isset($this->contactMapper)) {
            $this->contactMapper = new C3op_Register_ContactMapper($this->db);
        }
    }



    private function initReceivableMapper()
    {
        if (!isset($this->receivableMapper)) {
            $this->receivableMapper = new C3op_Projects_ReceivableMapper($this->db);
        }
    }

    private function initReceivableWithCheckedId(C3op_Projects_ReceivableMapper $mapper)
    {
        return $mapper->findById($this->checkIdFromGet());
    }

    private function checkIdFromGet()
    {
        $data = $this->_request->getParams();
        $filters = array(
            'id' => new Zend_Filter_Alnum(),
        );
        $validators = array(
            'id' => array('Digits', new Zend_Validate_GreaterThan(0)),
        );
        $input = new Zend_Filter_Input($filters, $validators, $data);
        if ($input->isValid()) {
            $id = $input->id;
            return $id;
        }
        throw new C3op_Projects_OutlayException("Invalid Action Id from Get");

    }

    private function setDateValueToFormField(Zend_Form $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        if ($value != '0000-00-00')  {
            $field->setValue(C3op_Util_DateDisplay::FormatDateToShow($value));
        } else {
            $field->setValue("");
        }
    }

}