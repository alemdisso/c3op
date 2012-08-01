<?php

class Projects_HumanResourceController extends Zend_Controller_Action
{
    private $humanResourceMapper;
    private $actionMapper;
    private $db;

    public function init()
    {
        $this->db = Zend_Registry::get('db');
    }

    public function createAction()
    {
        // cria form
        $form = new C3op_Form_HumanResourceCreate();
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');
                $this->_redirect('/projects/human-resource/success-create/?id=' . $id);
            } else throw new C3op_Projects_ActionException("An action must have a valid title.");
        } else {
            $data = $this->_request->getParams();
            $actionId = $data['actionId'];
            if (!isset($this->actionMapper)) {
                $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
            }
            $thisAction = $this->actionMapper->findById($actionId);
            $this->view->actionTitle = $thisAction->GetTitle();
            $this->view->linkActionDetail = "/projects/action/detail/?id=" . $thisAction->getId();
            $actionField = $form->getElement('action');
            $actionField->setValue($actionId);
            $contactField = $form->getElement('contact');
            if (!isset($this->contactMapper)) {
                $this->contactMapper = new C3op_Register_ContactMapper($this->db);
            }
            $allContacts = $this->contactMapper->getAllIds();

            while (list($key, $contactId) = each($allContacts)) {
                $eachContact = $this->contactMapper->findById($contactId);
                $contactField->addMultiOption($contactId, $eachContact->GetName());
            }            
        }
    }

    public function editAction()
    {
        $form = new C3op_Form_HumanResourceEdit;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/projects/human-resource/success-create/?id=' . $id);
            } else throw new C3op_Projects_ProjectException("Invalid data for new human resource.");
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
                if (!isset($this->humanResourceMapper)) {
                    $this->humanResourceMapper = new C3op_Projects_HumanResourceMapper($this->db);
                }
                $thisHumanResource = $this->humanResourceMapper->findById($id);
                $descriptionField = $form->getElement('description');
                $descriptionField->setValue($thisHumanResource->getDescription());
                $contactField = $form->getElement('contact');
                $contactField->setValue($thisHumanResource->getContact());
                $idField = $form->getElement('id');
                $idField->setValue($id);
                $valueField = $form->getElement('value');
                $valueField->setValue($thisHumanResource->getValue());
                $actionField = $form->getElement('action');
                $actionField->setValue($thisHumanResource->getAction());
                if (!isset($this->actionMapper)) {
                    $this->actionMapper = new C3op_Projects_ActionMapper($this->db);
                }
                $thisAction = $this->actionMapper->findById($thisHumanResource->getAction());
                
                $contactField = $form->getElement('contact');            
                if (!isset($this->contactMapper)) {
                    $this->contactMapper = new C3op_Register_ContactMapper($this->db);
                }
                $allContacts = $this->contactMapper->getAllIds();

                while (list($key, $contactId) = each($allContacts)) {
                    $eachContact = $this->contactMapper->findById($contactId);
                    $contactField->addMultiOption($contactId, $eachContact->GetName());
                }            
                $contactField->setValue($thisHumanResource->getContact());

                $this->view->actionTitle = $thisAction->GetTitle();
                $this->view->linkActionDetail = "/projects/action/detail/?id=" . $thisHumanResource->getAction();
                
            }

        }
    }

    public function contractAction()
    {
        $form = new C3op_Form_HumanResourceContract;
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData)) {
                $id = $form->process($postData);
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage('The record was successfully updated.');          
                $this->_redirect('/projects/human-resource/success-create/?id=' . $id);
            } else throw new C3op_Projects_ProjectException("Invalid data.");
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
                $this->initHumanResourceMapper();
                $thisHumanResource = $this->humanResourceMapper->findById($id);
                $idField = $form->getElement('id');
                $idField->setValue($id);
                $this->initActionMapper();
                $thisAction = $this->actionMapper->findById($thisHumanResource->getAction());
                
                $this->SetDateValueToFormField($form, 'predictedBeginDate', $thisAction->GetPredictedBeginDate());
                $this->SetDateValueToFormField($form, 'predictedFinishDate', $thisAction->GetPredictedFinishDate());
                $contactField = $form->getElement('contact');            
                if (!isset($this->contactMapper)) {
                    $this->contactMapper = new C3op_Register_ContactMapper($this->db);
                }
                $allContacts = $this->contactMapper->getAllIds();

                $this->view->actionTitle = $thisAction->GetTitle();
                $this->view->linkActionDetail = "/projects/action/detail/?id=" . $thisHumanResource->getAction();
                
            }

        }
    }

    public function outlaysAction()
    {
        $outlayMapper = new C3op_Projects_OutlayMapper($this->db);
        if (!isset($this->humanResourceMapper)) {
            $this->humanResourceMapper = new C3op_Projects_HumanResourceMapper($this->db);
        }

        $id = $this->checkIdFromGet();
        $thisHumanResource = $this->humanResourceMapper->findById($id);
        $outlaysIdList = $this->humanResourceMapper->getAllOutlays($thisHumanResource);
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
                'editLink' => '/projects/outlay/edit/?id=' . $outlayId   ,
            );
        }
        
        $humanResourceInfo = array(
            'title' => 'provisório...',
            'linkDetail' => '/projects/project/detail/?id=' . 0 ,
            'outlaysList' => $outlaysList,
        );

        $this->view->humanResourceInfo = $humanResourceInfo;
    }

    
    
    public function sucessAction()
    {
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper
                ->getHelper('FlashMessenger')
                ->getMessages();
        } else {
            $this->_redirect('/');
        }
    }
    
    public function successCreateAction()
    {
        $this->initHumanResourceMapper();
        $humanResource =  $this->initHumanResourceWithCheckedId($this->humanResourceMapper);
        $actionRelated = $humanResource->GetAction();
        if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();    
            $this->getResponse()->setHeader('Refresh', '3; URL=/projects/action/detail/?id=' . $actionRelated);
        } else {
            $this->_redirect('/projects');    
        } 
    }

    public function errorEditAction()
    {
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $flashMessenger->setNamespace('messages');
        $this->view->messages = $flashMessenger->getMessages();
        $flashMessenger->addMessage('Id Inválido');
    }

   public function dismissContactAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $this->initHumanResourceMapper();
        $this->initActionMapper();        
        $humanResource =  $this->initHumanResourceWithCheckedId($this->humanResourceMapper);
        $action = $this->actionMapper->findById($humanResource->GetContact());
        $dismissal = new C3op_Projects_HumanResourceDismissal();
        $dismissal->ContactDismiss($action, $humanResource, $this->humanResourceMapper);

        echo 'Contato dispensado';
    }  
  
//   public function contractContactAction()
//    {
//        $this->_helper->layout->disableLayout();
//        $this->_helper->viewRenderer->setNoRender(TRUE);
//
//        $this->initHumanResourceMapper();
//        $this->initActionMapper();        
//        $humanResource =  $this->initHumanResourceWithCheckedId($this->humanResourceMapper);
//        $action = $this->actionMapper->findById($humanResource->GetContact());
//        $contracting = new C3op_Projects_HumanResourceContracting();
//        $contracting->ContactContract($action, $humanResource, $this->humanResourceMapper);
//
//        echo 'Contratação confirmada';
//    }  
  
    
    
    private function initHumanResourceWithCheckedId(C3op_Projects_HumanResourceMapper $mapper)
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

    private function populateContactsField($humanResourceId, C3op_Form_HumanResourceCreate $form, $contactId = 0)
    {
        $validator = new C3op_Util_ValidId();
        $parentActionId = 0;
        if ($validator->isValid($humanResourceId)) {
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
                $thisProject = $this->projectMapper->findById($humanResourceId);
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

   private function initHumanResourceMapper()
    {
         $this->humanResourceMapper = new C3op_Projects_HumanResourceMapper($this->db);
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