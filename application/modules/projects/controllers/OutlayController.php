<?php

class Projects_OutlayController  extends Zend_Controller_Action
{
    private $actionMapper;
    private $teamMemberMapper;
    private $projectMapper;
    private $outlayMapper;
    private $contactMapper;
    private $linkageMapper;
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
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/outlay/success/?id=' . $postData['action']);
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
                    $this->teamMemberMapper = new C3op_Projects_TeamMemberMapper($this->db);
                }
                $outlayTeamMember = $this->teamMemberMapper->findById($teamMemberId);
                $this->populateFieldsAssociatedToTeamMember($outlayTeamMember, $form);
            } else {
                throw new C3op_Projects_TeamMemberException("Um desembolso precisa estar associado a um recurso.");
                $teamMemberId = 0;
                $projectId = $data['project'];
            }

        }
        $this->view->pageData = $this->pageData;
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
                $this->_redirect('/projects/outlay/success/?id=' . $postData['action']);
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
                    $this->teamMemberMapper = new C3op_Projects_TeamMemberMapper($this->db);
                }

                $outlayTeamMember = $this->teamMemberMapper->findById($thisOutlay->GetTeamMember());
                $this->populateFieldsAssociatedToTeamMember($outlayTeamMember, $form);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'id', $id);
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'project', $thisOutlay->GetProject());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'action', $thisOutlay->GetAction());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'teamMember', $thisOutlay->GetTeamMember());
                $this->setDateValueToFormField($form, 'predictedDate', $thisOutlay->GetPredictedDate());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'predictedValue', $thisOutlay->GetPredictedValue());
                C3op_Util_FormFieldValueSetter::SetValueToFormField($form, 'observation', $thisOutlay->GetObservation());

            }

        }
        $this->view->pageData = $this->pageData;
    }
    public function notifyAction()
    {
        // cria form
        $form = new C3op_Form_OutlayNotify;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/outlay/success/?id=' . $id);
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
                $projectId = $this->populateProjectFields($outlayToBeNotified->GetProject(), $form);
            }

        }
    }


    private function populateFieldsAssociatedToTeamMember(C3op_Projects_TeamMember $teamMember, C3op_Form_OutlayCreate $form)
    {

        $teamMemberField = $form->getElement('teamMember');
        $teamMemberField->setValue($teamMember->Getid());

        $this->pageData['contactName'] = $this->view->translate("#(not defined)");
        $this->pageData['teamMemberId'] = null;



        if ($teamMember->getLinkage() > 0) {
            if (!isset($this->linkageMapper)) {
                $this->initLinkageMapper();
            }
            if (!isset($this->contactMapper)) {
                $this->initContactMapper();
            }

            $teamMemberLinkage = $this->linkageMapper->findById($teamMember->getLinkage());
            $teamMemberContact = $this->contactMapper->findById($teamMemberLinkage->getContact());

            $this->pageData['contactName'] = $teamMemberContact->GetName();
            $this->pageData['teamMemberId'] = $teamMember->Getid();
        }

        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }
        $actionField = $form->getElement('action');
        $actionField->setValue($teamMember->GetAction());

        $teamMemberAction = $this->actionMapper->findById($teamMember->GetAction());
        $this->pageData['actionTitle'] = $teamMemberAction->GetTitle();
        $this->pageData['actionId'] = $teamMemberAction->GetId();

        $projectField = $form->getElement('project');
        $projectField->setValue($teamMemberAction->GetProject());

        if (!isset($this->projectMapper)) {
            $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }
        $thisProject = $this->projectMapper->findById($teamMemberAction->GetProject());
        $this->pageData['projectTitle'] = $thisProject->GetShortTitle();
        $this->pageData['projectId'] = $thisProject->GetId();

   }

    public function successAction()
    {

        $this->initActionMapper();
        $action =  $this->initActionWithCheckedId($this->actionMapper);
        $actionRelatedId = $action->GetId();

        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
            $this->getResponse()->setHeader('Refresh', '1; URL=/projects/action/detail/?id=' . $actionRelatedId);
        } else {
            $this->_redirect('/projects/action/detail/?id=' . $actionRelatedId);
        }
    }

    private function initOutlayMapper()
    {
        if (!isset($this->outlayMapper)) {
            $this->outlayMapper = new C3op_Projects_OutlayMapper($this->db);
        }
    }

    private function initActionMapper()
    {
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }
    }


    private function initActionWithCheckedId(C3op_Projects_ActionMapper $mapper)
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


    private function initContactMapper()
    {
         $this->contactMapper = new C3op_Register_ContactMapper($this->db);
    }

    private function initLinkageMapper()
    {
         $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
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


    private function initOutlayWithCheckedId(C3op_Projects_OutlayMapper $mapper)
    {
        return $mapper->findById($this->checkIdFromGet());
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
}

