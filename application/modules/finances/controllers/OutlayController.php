<?php

class Finances_OutlayController  extends Zend_Controller_Action
{
    private $actionMapper;
    private $contactMapper;
    private $institutionMapper;
    private $outlayMapper;
    private $projectMapper;
    private $responsibleMapper;

    private $pageData;

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
        $form = new C3op_Form_OutlayCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The outlay was successfully created.'));
                $this->_redirect('/finances/project/detail/?id=' . $postData['project']);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();
            if (isset($data['responsible'])) {
                $responsibleId = $data['responsible'];
                $this->initResponsibleMapper();
                $outlayResponsible = $this->responsibleMapper->findById($responsibleId);
                $this->populateHiddenFieldsAssociatedToResponsible($outlayResponsible, $form);
                $responsibleData = $this->fetchResponsibleData($outlayResponsible);
            } else {
                throw new C3op_Resources_ResponsibleException("Um desembolso precisa estar associado a um recurso.");
                $responsibleId = 0;
                $projectId = $data['project'];
            }

        }
        $this->view->pageData = $responsibleData;
    }

    public function editAction()
    {
        $form = new C3op_Form_OutlayEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/action/detail/?id=' . $postData['action']);
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
                if (!isset($this->outlayMapper)) {
                    $this->initOutlayMapper();
                }
                $thisOutlay = $this->outlayMapper->findById($id);
                $this->initResponsibleMapper();

                $outlayResponsible = $this->responsibleMapper->findById($thisOutlay->GetResponsible());

                $responsibleData = $this->fetchResponsibleData($outlayResponsible);

                //$responsibleData = $this->populateFieldsAssociatedToResponsible($outlayResponsible, $form);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'project', $thisOutlay->GetProject());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'action', $thisOutlay->GetAction());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'responsible', $thisOutlay->GetResponsible());
                $this->setDateValueToFormField($form, 'predictedDate', $thisOutlay->GetPredictedDate());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'predictedValue', $thisOutlay->GetPredictedValue());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'observation', $thisOutlay->GetObservation());

            }

        }
        $this->view->pageData = $responsibleData;
    }
    public function notifyAction()
    {
        // cria form
        $form = new C3op_Form_OutlayNotify;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $outlayMapper = new C3op_Finances_OutlayMapper($this->db);
            $outlayBeingNotified = $outlayMapper->FindById($postData['id']);
            $actionId = $outlayBeingNotified->getAction();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The payment was successfully notified.'));
                $this->_redirect('/projects/action/detail/?id=' . $actionId);
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
                $this->initOutlayMapper();
                $outlayToBeNotified =  $this->initOutlayWithCheckedId($this->outlayMapper);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $outlayToBeNotified->getId());
//                $this->setDateValueToFormField($form, 'realDate', $thisOutlay->GetRealDate());
//                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'realValue', $thisOutlay->GetRealValue());
                $projectId = $outlayToBeNotified->GetProject();
                $data = $this->getOutlayData($outlayToBeNotified);
                $this->populateProjectFields($projectId, $form);
                $this->view->pageData = $data;
            }

        }
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
        throw new C3op_Finances_OutlayException("Invalid Action Id from Get");

    }

    private function fetchResponsibleData(C3op_Resources_Responsible $responsible)
    {

        if (!isset($this->outlayMapper)) {
            $this->initOutlayMapper();
        }
        if (!isset($this->institutionMapper)) {
            $this->initInstitutionMapper();
        }
        if (!isset($this->contactMapper)) {
            $this->initContactMapper();
        }
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }
        if (!isset($this->projectMapper)) {
            $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }

        $responsibleData = array();

        $contactName = $this->view->translate("#(not defined)");
        $responsibleId = null;

        if ($responsible->getContact() > 0) {
            $responsibleContact = $this->contactMapper->findById($responsible->getContact());
            $contactName = $responsibleContact->GetName();
            $responsibleId = $responsible->Getid();
        }

        $responsibleAction = $this->actionMapper->findById($responsible->GetAction());
        $actionTitle = $responsibleAction->GetTitle();
        $actionId = $responsibleAction->GetId();

        $thisProject = $this->projectMapper->findById($responsibleAction->GetProject());
        $projectTitle = $thisProject->GetShortTitle();
        $projectId = $thisProject->GetId();

        $currencyDisplay = new  C3op_Util_CurrencyDisplay();

        $payedValue = $this->outlayMapper->totalPayedValueForResponsible($responsible);
        if ($payedValue === null) {
            $payedValue = "0.00";
        }
        $payedValue = $currencyDisplay->FormatCurrency($payedValue);

        if (!is_null($responsible->getContractedValue())) {
            $totalValue = $currencyDisplay->FormatCurrency($responsible->getContractedValue());
        } else {
            $totalValue = $currencyDisplay->FormatCurrency($responsible->getPredictedValue());
        }

        $actionStatus = $responsibleAction->getStatus();
        $statusTypes = new C3op_Projects_ActionStatusTypes();
        $actionStatusLabel = $this->view->translate($statusTypes->TitleForType($actionStatus));

        $validator = new C3op_Util_ValidDate();
        if (($validator->isValid($responsibleAction->getRealBeginDate())) && ($validator->isValid($responsibleAction->getRealFinishDate()))) {
            $beginDate = C3op_Util_DateDisplay::FormatDateToShow($responsibleAction->getRealBeginDate());
            $finishDate = C3op_Util_DateDisplay::FormatDateToShow($responsibleAction->getRealFinishDate());
            $durationMessage = sprintf($this->view->translate("#From %s until %s"), $beginDate, $finishDate);
        } else if (($validator->isValid($responsibleAction->getRealBeginDate())) && ($validator->isValid($responsibleAction->getPredictedFinishDate()))) {
            $beginDate = C3op_Util_DateDisplay::FormatDateToShow($responsibleAction->getRealBeginDate());
            $finishDate = C3op_Util_DateDisplay::FormatDateToShow($responsibleAction->getPredictedFinishDate());
            $durationMessage = sprintf($this->view->translate("#Began in %s, predicted to finish until %s"), $beginDate, $finishDate);
        } else if (($validator->isValid($responsibleAction->getPredictedBeginDate())) && ($validator->isValid($responsibleAction->getPredictedFinishDate()))) {
            $beginDate = C3op_Util_DateDisplay::FormatDateToShow($responsibleAction->getPredictedBeginDate());
            $finishDate = C3op_Util_DateDisplay::FormatDateToShow($responsibleAction->getPredictedFinishDate());
            $durationMessage = sprintf($this->view->translate("#Predicted to begin in %s, and to finish until %s"), $beginDate, $finishDate);
        } else{
            $durationMessage = $this->view->translate("#Undefined dates");
        }

        $responsibleData = array(
            'contactName'     => $contactName,
            'responsibleId'    => $responsibleId,
            'actionTitle'     => $actionTitle,
            'actionId'        => $actionId,
            'projectTitle'    => $projectTitle,
            'projectId'       => $projectId,
            'budgetAvailable' => "$payedValue/$totalValue",
            'duration'        => $durationMessage,
            'status'          => $actionStatusLabel,
            'outlayValue'     => $totalValue,
        );

        return $responsibleData;

   }

    private function getOutlayData(C3op_Finances_Outlay $outlay)
    {
        $projectId = $outlay->getProject();

        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $this->initProjectMapper();
            $theProject = $this->projectMapper->findById($projectId);
            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $predictedValue = $currencyDisplay->FormatCurrency($outlay->getPredictedValue());
            $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($outlay->getPredictedDate());

            $this->initResponsibleMapper();
            $responsibleId = $outlay->getResponsible();
            $theResponsible = $this->responsibleMapper->findById($responsibleId);
            $payeeName = $this->view->translate("#Not defined");

//            $linkageId = $theResponsible->GetLinkage();
//            if ($linkageId > 0) {
//                $this->initContactMapper();
//                $this->initLinkageMapper();
//                $linkageContact = $this->linkageMapper->findById($linkageId);
//                $payeeId = $linkageContact->GetContact();
//                $contractedContact = $this->contactMapper->findById($payeeId);
//                $payeeName = $contractedContact->GetName();
//            }
//

            $payeeId = null;
            $contactId = null;
            $institutionId = null;

            if (($theResponsible->getType() == C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER)
                 && ($theResponsible->getContact() > 0)) {
                $this->initContactMapper();
                $responsibleContact = $this->contactMapper->findById($theResponsible->getContact());
                $payeeName = $responsibleContact->getName();
                $payeeId = $responsibleContact->getId();
                $responsibleId = $theResponsible->getId();
            }

            if (($theResponsible->getType() == C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE)
                 && ($theResponsible->getInstitution() > 0)) {
                $this->initInstitutionMapper();
                $responsibleInstitution = $this->institutionMapper->findById($theResponsible->getInstitution());
                $payeeName = $responsibleInstitution->getShortName();
                $responsibleId = $theResponsible->getId();
                $payeeId = $theResponsible->getInstitution();
            }


            $outlayDetails = sprintf ($this->view->translate("#Paying to %s, %s predicted for %s"), $payeeName, $predictedValue, $predictedDate);

            $data = array(
                'projectTitle'      => $theProject->GetShortTitle(),
                'projectId'         => $projectId,
                'outlayDetails'     => $outlayDetails,
            );

            return $data;
        } else throw new C3op_Finances_OutlayException("Outlay needs a positive integer project id.");

    }


    private function initActionMapper()
    {
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }
    }

    private function initContactMapper()
    {
         $this->contactMapper = new C3op_Register_ContactMapper($this->db);
    }

    private function initInstitutionMapper()
    {
         $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
    }


    private function initOutlayMapper()
    {
        if (!isset($this->outlayMapper)) {
            $this->outlayMapper = new C3op_Finances_OutlayMapper($this->db);
        }
    }

    private function initOutlayWithCheckedId(C3op_Finances_OutlayMapper $mapper)
    {
        return $mapper->findById($this->checkIdFromGet());
    }

    private function initProjectMapper()
    {
        if (!isset($this->projectMapper)) {
            $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }
    }

    private function initResponsibleMapper()
    {
        if (!isset($this->responsibleMapper)) {
            $this->responsibleMapper = new C3op_Resources_ResponsibleMapper($this->db);
        }
    }

    private function populateHiddenFieldsAssociatedToResponsible(C3op_Resources_Responsible $responsible, C3op_Form_OutlayCreate $form)
    {

        if (!isset($this->outlayMapper)) {
            $this->initOutlayMapper();
        }
        if (!isset($this->actionMapper)) {
            $this->initActionMapper();
        }

        $element = $form->getElement('responsible');
        $element->setValue($responsible->Getid());

        $element = $form->getElement('action');
        $element->setValue($responsible->GetAction());

        $responsibleAction = $this->actionMapper->findById($responsible->GetAction());
        $element = $form->getElement('project');
        $element->setValue($responsibleAction->GetProject());

        $payedValue = $this->outlayMapper->totalPayedValueForResponsible($responsible);
        if ($payedValue === null) {
            $payedValue = "0.00";
        }

        $predictedValue = $responsible->getContractedValue() - $payedValue;
        // responsible was contracted, but outlay is still predicted
        $element = $form->getElement('predictedValue');
        $element->setValue($predictedValue);

   }

    private function populateProjectFields($projectId, Zend_Form $form)
    {
        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $projectField = $form->getElement('project');
            $projectField->setValue($projectId);
        } else throw new C3op_Finances_OutlayException("Outlay needs a positive integer project id.");

    }

    private function setDateValueToFormField(Zend_Form $form, $fieldName, $value)
    {
        $field = $form->getElement($fieldName);
        $validator = new C3op_Util_ValidDate();
        if ((!is_null($value)) && ($validator->isValid($value))) {
            $field->setValue(C3op_Util_DateDisplay::FormatDateToShow($value));
        } else {
            $field->setValue("");
        }
    }






}

