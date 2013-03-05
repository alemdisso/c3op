<?php

class Resources_ResponsibleController extends Zend_Controller_Action
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

    public function assignedAction()
    {
        $this->_helper->layout->disableLayout();

        $data = $this->_request->getParams();

        $actionId = $data['actionId'];

        $this->initActionMapper();
        $action = $this->actionMapper->findById($actionId);

        $obj = new C3op_Projects_ActionResponsible($action, $this->actionMapper, $this->db);

        if ($obj->doesItHasAResponsible()) {
            $result = $obj->fetch();

            $pageData = array(
                'responsibleId'   => $result['responsibleId'],
                'responsibleName' => $result['responsibleName'],
                'projectId'       => $action->GetProject(),
            );

        } else {
            throw new C3op_Projects_ActionException("There should be an assigned responsible but couldn\'t find it");
        }

        $this->view->pageData = $pageData;

    }

    public function createAction()
    {
        //$this->_helper->layout->disableLayout();

        // cria form
        $form = new C3op_Form_ResponsibleCreate;
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $actionId = $form->process($postData);


                //$this->_helper->viewRenderer->setNoRender(TRUE);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The team member was created.'));
                //echo "show de bola...";
                $this->_redirect('/resources/responsible/assigned/?actionId=' . $actionId);
            } else {
                //form error: populate and go back
                $actionId = $postData['action'];
                $result = $this->givenActionIdGetActionAndProjectObjects($actionId);
                $parentAction = $result['actionObj'];
                $projectAction = $result['projectObj'];

                $form->populate($postData);
                $this->view->form = $form;
            }
        } else {
            $data = $this->_request->getParams();

            $actionId = $data['id'];

            if (!isset($this->actionMapper)) {
                $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
            }
            if (!isset($this->projectMapper)) {
                $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
            }
            $parentAction = $this->actionMapper->findById($actionId);
            $projectId = $parentAction->getProject();
            $projectAction = $this->projectMapper->findById($projectId);

            $actionField = $form->getElement('action');
            $actionField->setValue($actionId);
            $projectField = $form->getElement('project');
            $projectField->setValue($projectId);
            $linkageField = $form->getElement('linkage');
            if (!isset($this->contactMapper)) {
                $this->contactMapper = new C3op_Register_ContactMapper($this->db);
            }
            $allLinkedContacts = $this->contactMapper->getAllContactsThatAreLinkedToAnyInstitution();

            while (list($key, $linkageData) = each($allLinkedContacts)) {
                $linkageLabel = $linkageData['name'] . " ({$linkageData['short_name']})";
                $linkageField->addMultiOption($linkageData['linkage'], $linkageLabel);
            }

            $institutionField = $form->getElement('institution');
            if (!isset($this->institutionMapper)) {
                $this->institutionMapper = new C3op_Register_InstitutionMapper($this->db);
            }
            $allInstitutions = $this->institutionMapper->getAllServiceSuppliers();

            while (list($key, $institutionData) = each($allInstitutions)) {
                $institutionLabel = $institutionData['short_name'];
                $institutionField->addMultiOption($institutionData['id'], $institutionLabel);
            }

        }

        $pageData = array(
            'actionId' => $actionId,
            'actionTitle' => $parentAction->GetTitle(),
            'projectId' => $parentAction->GetProject(),
            'projectTitle' => $projectAction->GetShortTitle(),
        );

        $this->view->pageData = $pageData;

    }

    public function editAction()
    {
        $form = new C3op_Form_TeamMemberEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->initTeamMemberMapper();
                $teamMember =  $this->teamMemberMapper->findById($id);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($this->view->translate('#The record was successfully updated.'));
                $this->_redirect('/projects/action/detail/?id=' . $teamMember->GetAction());
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

    private function givenActionIdGetActionAndProjectObjects($actionId)
    {
        if (!isset($this->actionMapper)) {
            $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
        }
        if (!isset($this->projectMapper)) {
            $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);
        }
        $parentAction = $this->actionMapper->findById($actionId);
        $projectId = $parentAction->getProject();
        $projectAction = $this->projectMapper->findById($projectId);

        $data = array(
                    'actionId' => $actionId,
                    'actionObj' => $actionObj,
                    'projectId' => $projectId,
                    'actionId' => $projectAction,
                );

        return $data;


    }


    private function initActionWithCheckedId(C3op_Projects_ActionMapper $mapper)
    {
        return $mapper->findById($this->checkIdFromGet());
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