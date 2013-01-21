<?php

class Resources_TeamMemberController extends Zend_Controller_Action
{
    private $teamMemberMapper;
    private $actionMapper;
    private $contactMapper;
    private $linkageMapper;
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
        $form = new C3op_Form_TeamMemberCreate();
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/resources/team-member/success/?id=' . $id);
            } else {
                //form error: populate and go back
                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();
            $actionId = $data['actionId'];
            if (!isset($this->actionMapper)) {
                $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
            }
            if (!isset($this->projectMapper)) {
                $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
            }
            $parentAction = $this->actionMapper->findById($actionId);
            $projectAction = $this->projectMapper->findById($parentAction->getProject());

            $pageData = array(
                'actionId' => $actionId,
                'actionTitle' => $parentAction->GetTitle(),
                'projectId' => $parentAction->GetProject(),
                'projectTitle' => $projectAction->GetShortTitle(),
            );

            $this->view->pageData = $pageData;

            $actionField = $form->getElement('action');
            $actionField->setValue($actionId);
            $linkageField = $form->getElement('linkage');
            if (!isset($this->contactMapper)) {
                $this->contactMapper = new C3op_Register_ContactMapper($this->db);
            }
            $allLinkedContacts = $this->contactMapper->getAllContactsThatAreLinkedToAnyInstitution();

            while (list($key, $linkageData) = each($allLinkedContacts)) {
                $linkageLabel = $linkageData['name'] . " ({$linkageData['short_name']})";
                $linkageField->addMultiOption($linkageData['linkage'], $linkageLabel);
            }
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_TeamMemberEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/resources/team-member/success/?id=' . $id);
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
                if (!isset($this->teamMemberMapper)) {
                    $this->teamMemberMapper = new C3op_Resources_TeamMemberMapper($this->db);
                }
                $thisTeamMember = $this->teamMemberMapper->findById($id);
                $descriptionField = $form->getElement('description');
                $descriptionField->setValue($thisTeamMember->getDescription());
                $linkageField = $form->getElement('linkage');
                $linkageField->setValue($thisTeamMember->getLinkage());
                $idField = $form->getElement('id');
                $idField->setValue($id);
                $valueField = $form->getElement('value');
                $valueField->setValue($thisTeamMember->getValue());
                $actionField = $form->getElement('action');
                $actionField->setValue($thisTeamMember->getAction());
                if (!isset($this->actionMapper)) {
                    $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
                }
                $parentAction = $this->actionMapper->findById($thisTeamMember->getAction());

                $linkageField = $form->getElement('linkage');
                if (!isset($this->contactMapper)) {
                    $this->contactMapper = new C3op_Register_ContactMapper($this->db);
                }
                $allLinkedContacts = $this->contactMapper->getAllContactsThatAreLinkedToAnyInstitution();

                while (list($key, $linkageData) = each($allLinkedContacts)) {
                    $linkageLabel = $linkageData['name'] . " ({$linkageData['short_name']})";
                    $linkageField->addMultiOption($linkageData['linkage'], $linkageLabel);
                }
                $linkageField->setValue($thisTeamMember->getLinkage());

                if (!isset($this->projectMapper)) {
                    $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
                }
                $projectAction = $this->projectMapper->findById($parentAction->getProject());

                $pageData = array(
                    'actionId' => $thisTeamMember->GetAction(),
                    'actionTitle' => $parentAction->GetTitle(),
                    'projectId' => $parentAction->GetProject(),
                    'projectTitle' => $projectAction->GetShortTitle(),
                );

                $this->view->pageData = $pageData;
            }
        }
    }

    public function contractAction()
    {
        $form = new C3op_Form_TeamMemberContract;
        $headerData = array();
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/resources/team-member/success/?id=' . $id);
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
                $this->initTeamMemberMapper();
                $thisTeamMember = $this->teamMemberMapper->findById($id);

                $idField = $form->getElement('id');
                $idField->setValue($id);
                $this->initActionMapper();
                $thisAction = $this->actionMapper->findById($thisTeamMember->getAction());

                $this->setDateValueToFormField($form, 'predictedBeginDate', $thisAction->GetPredictedBeginDate());
                $this->setDateValueToFormField($form, 'predictedFinishDate', $thisAction->GetPredictedFinishDate());
                $contactField = $form->getElement('contact');
                $this->initContactMapper();
                $this->initLinkageMapper();

                $allContacts = $this->contactMapper->getAllIds();
                $linkage = $this->linkageMapper->findById($thisTeamMember->getLinkage());
                $contact = $this->contactMapper->findById($linkage->getContact());
                $contactName = $contact->GetName();

                $actionTitle = $thisAction->GetTitle();
               $headerData = array(
                   'teamMemberName' => $contactName,
                   'actionTitle'    => $actionTitle,
               ) ;

            }

        }
        $this->view->headerData = $headerData;
    }

    public function outlaysAction()
    {
        $outlayMapper = new C3op_Finances_OutlayMapper($this->db);
        if (!isset($this->teamMemberMapper)) {
            $this->teamMemberMapper = new C3op_Resources_TeamMemberMapper($this->db);
        }

        $id = $this->checkIdFromGet();
        $thisTeamMember = $this->teamMemberMapper->findById($id);
        $outlaysIdList = $this->teamMemberMapper->getAllOutlays($thisTeamMember);
        $outlaysList = array();
        reset ($outlaysList);
        $outlaysTotalValue = 0;
        $outlaysCounter = 0;
        foreach ($outlaysIdList as $outlayId) {
            $thisOutlay = $outlayMapper->findById($outlayId);
            $outlaysCounter++;
            if ($thisOutlay->GetObservation()) {
                $observation = $thisOutlay->GetObservation();
            } else {
                $observation = "(#$outlaysCounter)";
            }

            if ($thisOutlay->GetPredictedValue()) {
                $value = $thisOutlay->GetPredictedValue();
            } else {
                $value = "???";
            }



            $outlaysList[$outlayId] = array(
                'observation' => $observation,
                'value' => $value,
                'editLink' => '/finances/outlay/edit/?id=' . $outlayId   ,
            );
        }

        $teamMemberInfo = array(
            'title' => 'provisório...',
            'linkDetail' => '/projects/project/detail/?id=' . 0 ,
            'outlaysList' => $outlaysList,
        );

        $this->view->teamMemberInfo = $teamMemberInfo;
    }

    public function removeAction()
    {
        $form = new C3op_Form_TeamMemberRemove();
        $this->view->form = $form;
        $this->initTeamMemberMapper();
        $this->initActionMapper();

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $submitButton = $form->getUnfilteredValue('Submit');

                if ($submitButton) {
                    $theTeamMember = $this->teamMemberMapper->findById($postData['id']);
                    $theAction = $this->actionMapper->findById($theTeamMember->getAction());
                    $projectTeamMember = $theAction->getProject();
                    $redirectTo = '/projects/project/detail/?id=' . $projectTeamMember;

                    $id = $form->process($postData);
                    $this->_helper->getHelper('FlashMessenger')
                        ->addMessage($this->view->translate('#The record was successfully removed.'));
                    $this->_redirect($redirectTo);
                } else {
                    $id = $postData['id'];
                    $theTeamMember = $this->teamMemberMapper->findById($id);
                    $theAction = $this->actionMapper->findById($theTeamMember->getAction());
                    $this->_redirect('/projects/project/detail/?id=' . $theAction->getProject());
                }
            } else {
                //form error: populate and go back
                $this->view->form = $form;
            }
        } else {
            // GET
            $id = $this->checkIdFromGet();
            $teamMemberData = array();
            $theTeamMember = $this->teamMemberMapper->findById($id);
            $theAction = $this->actionMapper->findById($theTeamMember->getAction());
            $idField = $form->getElement('id');
            $idField->setValue($id);
            $teamMemberData = array(
                'id'   => $id,
                'description' => $theTeamMember->getDescription(),
                'actionTitle' => $theAction->getTitle(),
            );
            $this->view->teamMemberData = $teamMemberData;
        }
    }

    public function successAction()
    {
        $this->initTeamMemberMapper();
        $teamMember =  $this->initTeamMemberWithCheckedId($this->teamMemberMapper);
        $actionRelated = $teamMember->GetAction();
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
            $this->getResponse()->setHeader('Refresh', '1; URL=/projects/action/detail/?id=' . $actionRelated);
        } else {
            $this->_redirect('/projects');
        }
    }

   public function dismissContactAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initTeamMemberMapper();
        $this->initActionMapper();
        $teamMember =  $this->initTeamMemberWithCheckedId($this->teamMemberMapper);
        $action = $this->actionMapper->findById($teamMember->GetAction());
        $dismissal = new C3op_Resources_TeamMemberDismissal();
        $dismissal->ContactDismiss($action, $teamMember, $this->teamMemberMapper);

        echo 'Contato dispensado';
    }

    private function initTeamMemberWithCheckedId(C3op_Resources_TeamMemberMapper $mapper)
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
        throw new C3op_Projects_ProjectException("Invalid Project Id from Get");

    }

    private function populateContactsField($teamMemberId, C3op_Form_TeamMemberCreate $form, $contactId = 0)
    {
        $validator = new C3op_Util_ValidId();
        $parentActionId = 0;
        if ($validator->isValid($teamMemberId)) {
            $subordinatedToField = $form->getElement('subordinatedTo');
            if (!isset($this->actionMapper)) {
                $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
            }

            if ($contactId > 0) {
                $thisAction = $this->actionMapper->findById($contactId);
                $parentActionId = $thisAction->GetSubordinatedTo();
                $allOtherActionsInProject = $this->actionMapper->getAllOtherActions($thisAction);

            } else {
                if (!isset($this->projectMapper)) {
                    $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
                }
                $thisProject = $this->projectMapper->findById($teamMemberId);
                $allOtherActionsInProject = $this->projectMapper->getAllActions($thisProject);
            }

            while (list($key, $contactId) = each($allOtherActionsInProject)) {
                $eachAction = $this->actionMapper->findById($contactId);
                $subordinatedToField->addMultiOption($contactId, $eachAction->GetTitle());
            }

            $subordinatedToField->setValue($parentActionId);

        } else throw new C3op_Projects_ActionException("Action needs a positive integer project id to find other actions.");
   }

    private function initActionMapper()
    {
         $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
    }

   private function initTeamMemberMapper()
    {
         $this->teamMemberMapper = new C3op_Resources_TeamMemberMapper($this->db);
    }

    private function initContactMapper()
    {
         $this->contactMapper = new C3op_Register_ContactMapper($this->db);
    }

    private function initLinkageMapper()
    {
         $this->linkageMapper = new C3op_Register_LinkageMapper($this->db);
    }

    private function initActionWithCheckedId(C3op_Projects_ActionMapper $mapper)
    {
        return $mapper->findById($this->checkIdFromGet());
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