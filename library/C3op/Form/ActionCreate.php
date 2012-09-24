<?php
class C3op_Form_ActionCreate extends Zend_Form
{
    public function init()
    {
        $this->setName('newActionForm')
            ->setAction('/projects/action/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('project');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('title');
        $titleValidator = new C3op_Projects_ProjectValidTitle();
        $element->setLabel(_('#Name:'))
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(true)
            ->addValidator($titleValidator)
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Checkbox('milestone');
        $element->setLabel('#This action is a milestone?') //'Essa ação é um marco do projeto?'
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column inset-by-ten omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('checked' => '1', 'unChecked' => '0'))
                ->setValue('0')
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Select('requirementForReceiving');
        $element->setLabel('#Is requirement for receiving: ') //'É requisito para receber: '
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'four columns')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
                ->setRegisterInArrayValidator(false);
        $element->addMultiOption(0, _("#(not a requirement for receiving)")); // (não é requisito para recebimento)
        $this->addElement($element);

        $element = new Zend_Form_Element_Select('subordinatedTo');
        $element->setLabel('#Subordinated to: ') //Subordinada a:
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'five columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
                ->setRegisterInArrayValidator(false);
        $element->addMultiOption(0, _("#(no action)"));
        $this->addElement($element);

        $element = new Zend_Form_Element_Select('responsible');
        $element->setLabel('#Responsible: ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
                ->setRegisterInArrayValidator(false);
        $element->addMultiOption(0, _("#(choose a person)")); //escolha uma pessoa
        $this->addElement($element);

        $element = new Zend_Form_Element_Textarea('description');
        $element->setLabel('#Description:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setAttrib('rows','8')
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('predictedBeginDate');
        $dateValidator = new C3op_Util_ValidDate();
        $element->setLabel('#Begin date:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator($dateValidator)
            ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('predictedFinishDate');
        $element->setLabel('#Finish date:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-three omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addValidator('date')
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($element);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('#Submit') //Gravar
               ->setDecorators(array('ViewHelper','Errors',
                    array(array('data' => 'HtmlTag'),
                    array('tag' => 'div','class' => 'two columns inset-by-nine omega')),
                    array('Label',
                      array('tag' => 'div','tagClass' => 'three columns alpha Invisible')
                    ),
                  ))
               ->setOptions(array('class' => 'submit Full alpha omega'));
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