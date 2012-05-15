<?php
class C3op_Form_ActionCreate extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('newActionForm')
            ->setAction('/projects/action/create')
            ->setMethod('post');
        
        $project = new Zend_Form_Element_Hidden('project');
        $project->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($project);

        
        // create text input for title
        $title = new Zend_Form_Element_Text('title');
        $titleValidator = new C3op_Projects_Util_ValidTitle();
        $title->setLabel('Nome:')
            ->setOptions(array('size' => '50'))
            ->setRequired(true)
            ->addValidator($titleValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($title);
        
        $milestone = new Zend_Form_Element_Checkbox('milestone');
        $milestone->setLabel('Essa ação é um marco do projeto?')
                ->addDecorator('Label', array('placement' => 'APPEND')) 
                ->setOptions(array('checked' => '1', 'unChecked' => '0'))
                ->setValue('0')
                ;
        $this->addElement($milestone);
        
        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Salvar')
            ->setOptions(array('class' => 'submit'));
        $this->addElement($submit);
                

    }
    
    public function process($data) {
        
        
        if ($this->isValid($data) !== true) 
        {
            throw new C3op_Form_ActionCreateException('Invalid data!');
        } 
        else
        {
            $db = Zend_Registry::get('db');
            $actionMapper = new C3op_Projects_ActionMapper($db);
            
            $action = new C3op_Projects_Action($this->project->GetValue());
            $action->SetTitle($this->title->GetValue());
            
            $milestone = $this->milestone->GetValue();
            if ($milestone == '1') {
                $action->SetMilestone(1);
            } else {
                $action->SetMilestone(0);
            }
            
            $actionMapper->insert($action);
        }
    }
 }