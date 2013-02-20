<?php

class Finances_OutlayController  extends Zend_Controller_Action
{
    private $actionMapper;
    private $contactMapper;
    private $linkageMapper;
    private $outlayMapper;
    private $projectMapper;
    private $teamMemberMapper;

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
                $this->_redirect('/projects/action/detail/?id=' . $postData['action']);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();
            if (isset($data['teamMember'])) {
                $teamMemberId = $data['teamMember'];
                if (!isset($this->teamMemberMapper)) {
                    $this->teamMemberMapper = new C3op_Resources_TeamMemberMapper($this->db);
                }
                $outlayTeamMember = $this->teamMemberMapper->findById($teamMemberId);
                $this->populateHiddenFieldsAssociatedToTeamMember($outlayTeamMember, $form);
                $teamMemberData = $this->fetchTeamMemberData($outlayTeamMember);
            } else {
                throw new C3op_Resources_TeamMemberException("Um desembolso precisa estar associado a um recurso.");
                $teamMemberId = 0;
                $projectId = $data['project'];
            }

        }
        $this->view->pageData = $teamMemberData;
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
                if (!isset($this->teamMemberMapper)) {
                    $this->teamMemberMapper = new C3op_Resources_TeamMemberMapper($this->db);
                }

                $outlayTeamMember = $this->teamMemberMapper->findById($thisOutlay->GetTeamMember());

                $teamMemberData = $this->fetchTeamMemberData($outlayTeamMember);

                //$teamMemberData = $this->populateFieldsAssociatedToTeamMember($outlayTeamMember, $form);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'project', $thisOutlay->GetProject());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'action', $thisOutlay->GetAction());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'teamMember', $thisOutlay->GetTeamMember());
                $this->setDateValueToFormField($form, 'predictedDate', $thisOutlay->GetPredictedDate());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'predictedValue', $thisOutlay->GetPredictedValue());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'observation', $thisOutlay->GetObservation());

            }

        }
        $this->view->pageData = $teamMemberData;
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

    private function fetchTeamMemberData(C3op_Resources_TeamMember $teamMember)
    {

        if (!isset($this->outlayMapper)) {
            $this->initOutlayMapper();
        }
        if (!isset($this->linkageMapper)) {
            $this->initLinkageMapper();
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

        $teamMemberData = array();

        $contactName = $this->view->translate("#(not defined)");
        $teamMemberId = null;

        if ($teamMember->getLinkage() > 0) {
            $teamMemberLinkage = $this->linkageMapper->findById($teamMember->getLinkage());
            $teamMemberContact = $this->contactMapper->findById($teamMemberLinkage->getContact());
            $contactName = $teamMemberContact->GetName();
            $teamMemberId = $teamMember->Getid();
        }

        $teamMemberAction = $this->actionMapper->findById($teamMember->GetAction());
        $actionTitle = $teamMemberAction->GetTitle();
        $actionId = $teamMemberAction->GetId();

        $thisProject = $this->projectMapper->findById($teamMemberAction->GetProject());
        $projectTitle = $thisProject->GetShortTitle();
        $projectId = $thisProject->GetId();

        $currencyDisplay = new  C3op_Util_CurrencyDisplay();

        $payedValue = $this->outlayMapper->totalPayedValueForTeamMember($teamMember);
        if ($payedValue === null) {
            $payedValue = "0.00";
        }
        $payedValue = $currencyDisplay->FormatCurrency($payedValue);
        $totalValue = $currencyDisplay->FormatCurrency($teamMember->getValue());

        $actionStatus = $teamMemberAction->getStatus();
        $statusTypes = new C3op_Projects_ActionStatusTypes();
        $actionStatusLabel = $this->view->translate($statusTypes->TitleForType($actionStatus));

        $validator = new C3op_Util_ValidDate();
        if (($validator->isValid($teamMemberAction->getRealBeginDate())) && ($validator->isValid($teamMemberAction->getRealFinishDate()))) {
            $beginDate = C3op_Util_DateDisplay::FormatDateToShow($teamMemberAction->getRealBeginDate());
            $finishDate = C3op_Util_DateDisplay::FormatDateToShow($teamMemberAction->getRealFinishDate());
            $durationMessage = sprintf($this->view->translate("#From %s until %s"), $beginDate, $finishDate);
        } else if (($validator->isValid($teamMemberAction->getRealBeginDate())) && ($validator->isValid($teamMemberAction->getPredictedFinishDate()))) {
            $beginDate = C3op_Util_DateDisplay::FormatDateToShow($teamMemberAction->getRealBeginDate());
            $finishDate = C3op_Util_DateDisplay::FormatDateToShow($teamMemberAction->getPredictedFinishDate());
            $durationMessage = sprintf($this->view->translate("#Began in %s, predicted to finish until %s"), $beginDate, $finishDate);
        } else if (($validator->isValid($teamMemberAction->getPredictedBeginDate())) && ($validator->isValid($teamMemberAction->getPredictedFinishDate()))) {
            $beginDate = C3op_Util_DateDisplay::FormatDateToShow($teamMemberAction->getPredictedBeginDate());
            $finishDate = C3op_Util_DateDisplay::FormatDateToShow($teamMemberAction->getPredictedFinishDate());
            $durationMessage = sprintf($this->view->translate("#Predicted to begin in %s, and to finish until %s"), $beginDate, $finishDate);
        } else{
            $durationMessage = $this->view->translate("#Undefined dates");
        }

        $teamMemberData = array(
            'contactName'     => $contactName,
            'teamMemberId'    => $teamMemberId,
            'actionTitle'     => $actionTitle,
            'actionId'        => $actionId,
            'projectTitle'    => $projectTitle,
            'projectId'       => $projectId,
            'budgetAvailable' => "$payedValue/$totalValue",
            'duration'        => $durationMessage,
            'status'          => $actionStatusLabel,
            'outlayValue'     => $totalValue,
        );

        return $teamMemberData;

   }

    private function getOutlayData(C3op_Finances_Outlay $outlay)
    {
        $projectId = $outlay->getProject();

        $validator = new C3op_Util_ValidId();
        if ($validator->isValid($projectId)) {
            $this->initProjectMapper();
            $thisProject = $this->projectMapper->findById($projectId);
            $currencyDisplay = new  C3op_Util_CurrencyDisplay();
            $predictedValue = $currencyDisplay->FormatCurrency($outlay->getPredictedValue());
            $predictedDate = C3op_Util_DateDisplay::FormatDateToShow($outlay->getPredictedDate());
            $outlayDetails = sprintf ($this->view->translate("#Paying %s predicted for %s"), $predictedValue, $predictedDate);

            $data = array(
                'projectTitle'      => $thisProject->GetShortTitle(),
                'projectId'         => $projectId,
                'outlayDetails' => $outlayDetails,
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

    private function initLinkageMapper()
    {
         $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
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

    private function populateHiddenFieldsAssociatedToTeamMember(C3op_Resources_TeamMember $teamMember, C3op_Form_OutlayCreate $form)
    {

        if (!isset($this->outlayMapper)) {
            $this->initOutlayMapper();
        }
        if (!isset($this->actionMapper)) {
            $this->initActionMapper();
        }

        $element = $form->getElement('teamMember');
        $element->setValue($teamMember->Getid());

        $element = $form->getElement('action');
        $element->setValue($teamMember->GetAction());

        $teamMemberAction = $this->actionMapper->findById($teamMember->GetAction());
        $element = $form->getElement('project');
        $element->setValue($teamMemberAction->GetProject());

        $payedValue = $this->outlayMapper->totalPayedValueForTeamMember($teamMember);
        if ($payedValue === null) {
            $payedValue = "0.00";
        }

        $predictedValue = $teamMember->getValue() - $payedValue;
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

