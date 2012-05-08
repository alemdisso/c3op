<?php
class C3op_Form_ProjectEdit extends C3op_Form_ProjectCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editProjectForm')
            ->setAction('/projects/project/edit')
            ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        
                        

    }
    
    public function process($data) {
        
        $db = Zend_Registry::get('db');
        $projectMapper = new C3op_Projects_ProjectMapper($db);        
        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ProjectEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $project = $projectMapper->findById($id);      
            $project->SetTitle($data['title']);
            
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($data['dateBegin']))
            {
                $converter = new C3op_Util_DateConverter();                
                $dateForMysql = $converter->convertDateToMySQLFormat($data['dateBegin']);
                $project->SetDateBegin($dateForMysql);
            }
            $project->SetValue($data['value']);
            $projectMapper->update($project);
        }
    }
 }