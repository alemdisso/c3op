<?php
class C3op_Form_ActionCreate extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('newActionForm')
            ->setAction('/projects/action/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');
        
        $project = new Zend_Form_Element_Hidden('project');
        $project->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($project);

        
        // create text input for title
        $title = new Zend_Form_Element_Text('title');
        $titleValidator = new C3op_Projects_ProjectValidTitle();
        $title->setLabel('Nome:')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->setRequired(true)
            ->addValidator($titleValidator)
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($title);
        
        $milestone = new Zend_Form_Element_Checkbox('milestone');
        $milestone->setLabel('Essa ação é um marco do projeto?')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column inset-by-ten omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('checked' => '1', 'unChecked' => '0'))
                ->setValue('0')
                ;
        $this->addElement($milestone);
                
        $requirementForReceiving = new Zend_Form_Element_Select('requirementForReceiving');
        $requirementForReceiving->setLabel('É requisito para receber: ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'four columns')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'four columns alpha omega'))
                ->setRegisterInArrayValidator(false);
        $requirementForReceiving->addMultiOption(0, "(não é requisito para recebimento)");
        $this->addElement($requirementForReceiving);
        
        $subordinatedTo = new Zend_Form_Element_Select('subordinatedTo');
        $subordinatedTo->setLabel('Subordinada a: ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'five columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
                ))
                ->setOptions(array('class' => 'five columns alpha omega'))
                ->setRegisterInArrayValidator(false);
        $subordinatedTo->addMultiOption(0, "nenhuma ação");
        $this->addElement($subordinatedTo);
        
        $responsible = new Zend_Form_Element_Select('responsible');
        $responsible->setLabel('Responsável: ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'eleven columns alpha omega'))
                ->setRegisterInArrayValidator(false);
        $responsible->addMultiOption(0, "escolha uma pessoa");
        $this->addElement($responsible);
        
        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Descrição:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setAttrib('rows','8')
            ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->setRequired(false)
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($description);
        
        $predictedBeginDate = new Zend_Form_Element_Text('predictedBeginDate');
        $dateValidator = new C3op_Util_ValidDate();
        $predictedBeginDate->setLabel('Data de início:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'three columns alpha omega'))
            ->setRequired(false)
            ->addValidator($dateValidator)
            ->addFilter('StringTrim');
        $this->addElement($predictedBeginDate);
        
        $predictedFinishDate = new Zend_Form_Element_Text('predictedFinishDate');
        $predictedFinishDate->setLabel('Data de término:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-three omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
            ))
            ->setOptions(array('class' => 'three columns alpha omega'))
            ->setRequired(false)
            ->addValidator('date')
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($predictedFinishDate);
        
        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Gravar')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'five columns inset-by-six omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Invisible')),
              ))
            ->setOptions(array('class' => 'submit two columns alpha omega'));
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
            
//            $action->SetTitle($this->title->GetValue());
            $action->SetTitle($data['title']);
            
            $action->SetStatus(C3op_Projects_ActionStatusConstants::STATUS_PLAN);
            $action->SetDescription($this->description->GetValue());
            $predictedBeginDate = $this->predictedBeginDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedBeginDate)) {
                $converter = new C3op_Util_DateConverter();                
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedBeginDate);
                $action->SetPredictedBeginDate($dateForMysql);
            }
            
            $predictedFinishDate = $this->predictedFinishDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedFinishDate)){
                $converter = new C3op_Util_DateConverter();                
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedFinishDate);
                $action->SetPredictedFinishDate($dateForMysql);
            }

            $action->SetSubordinatedTo($this->subordinatedTo->GetValue());
            $action->SetResponsible($this->responsible->GetValue());
            
            $milestone = $this->milestone->GetValue();
            if ($milestone == '1') {
                $action->SetMilestone(1);
            } else {
                $action->SetMilestone(0);
            }
            
            $requirementForReceiving = $this->requirementForReceiving->GetValue();
            if ($requirementForReceiving == '1') {
                $action->SetRequirementForReceiving(1);
            } else {
                $action->SetRequirementForReceiving(0);
            }

            $actionMapper->insert($action);
            return $action->getId();
        }
    }
 }