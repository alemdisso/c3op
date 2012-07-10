<?php
class C3op_Form_ActionEdit extends C3op_Form_ActionCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editActionForm')
            ->setAction('/projects/action/edit')
            ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        
        $this->removeElement('submit');
        
        $status = new Zend_Form_Element_Select('status');
        $status->setLabel('Status');
        $statusTypes = C3op_Projects_ActionStatusTypes::AllStatus();
        while (list($key, $title) = each($statusTypes)) {
            $status->addMultiOption($key, $title);
        }        
        $this->addElement($status);
        
        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Salvar')
            ->setOptions(array('class' => 'submit'));
        $this->addElement($submit);
                        

    }
    
    public function process($data) {
        
        $db = Zend_Registry::get('db');
        $actionMapper = new C3op_Projects_ActionMapper($db);        
        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ActionEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $action = $actionMapper->findById($id);      
            $action->SetTitle($data['title']);
            $action->SetProject($data['project']);
            $action->SetStatus($data['status']);
            $action->SetDescription($data['description']);
            $action->SetSubordinatedTo($data['subordinatedTo']);
            $action->SetResponsible($data['responsible']);
            $action->SetMilestone($data['milestone']);
            $action->SetRequirementForReceiving($data['requirementForReceiving']);
            
            $predictedBeginDate = $data['predictedBeginDate'];
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedBeginDate)) {
                $converter = new C3op_Util_DateConverter();                
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedBeginDate);
                $action->SetPredictedBeginDate($dateForMysql);
            }
            
            $predictedFinishDate = $data['predictedFinishDate'];
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedFinishDate)){
                $converter = new C3op_Util_DateConverter();                
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedFinishDate);
                $action->SetPredictedFinishDate($dateForMysql);
            }

            
            
            $actionMapper->update($action);
        }
    }
 }