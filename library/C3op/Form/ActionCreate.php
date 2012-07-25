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
        $titleValidator = new C3op_Projects_ProjectValidTitle();
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
                
        $requirementForReceiving = new Zend_Form_Element_Select('requirementForReceiving');
        $requirementForReceiving->setLabel('É requisito para receber: ')
                ->setRegisterInArrayValidator(false);
        $requirementForReceiving->addMultiOption(0, "(não é requisito para recebimento)");
        $this->addElement($requirementForReceiving);
        
        $subordinatedTo = new Zend_Form_Element_Select('subordinatedTo');
        $subordinatedTo->setLabel('Subordinada a: ')
                ->setRegisterInArrayValidator(false);
        $subordinatedTo->addMultiOption(0, "nenhuma ação");
        $this->addElement($subordinatedTo);
        
        $responsible = new Zend_Form_Element_Select('responsible');
        $responsible->setLabel('Responsável: ')
                ->setRegisterInArrayValidator(false);
        $responsible->addMultiOption(0, "escolha uma pessoa");
        $this->addElement($responsible);
        
        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Descrição:')
            ->setAttrib('cols','8')
            ->setAttrib('rows','5')
            ->setRequired(false)
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($description);
        
        $predictedBeginDate = new Zend_Form_Element_Text('predictedBeginDate');
        $dateValidator = new C3op_Util_ValidDate();
        $predictedBeginDate->setLabel('Data de início:')
            ->setOptions(array('size' => '35'))
            ->setRequired(false)
            ->addValidator($dateValidator)
            ->addFilter('StringTrim');
        $this->addElement($predictedBeginDate);
        
        $predictedFinishDate = new Zend_Form_Element_Text('predictedFinishDate');
        $predictedFinishDate->setLabel('Data de término:')
            ->setOptions(array('size' => '35'))
            ->setRequired(false)
            ->addValidator('date')
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($predictedFinishDate);
        
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