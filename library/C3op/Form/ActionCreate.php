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
        $element->setDecorators(array('ViewHelper'));

        $element = new Zend_Form_Element_Hidden('subordinatedTo');
        $element->addValidator('Int')
                ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

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
                ->addErrorMessage(_("#Name is required"))
                ->addValidator($titleValidator)
                ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Select('supervisor');
        $element->setLabel('#Supervisor: ')
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

        $element = new Zend_Form_Element_Text('baselineBeginDate');
        $dateValidator = new C3op_Util_ValidDate();
        $element->setLabel('#Baseline Begin date:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->setRequired(false)
            ->addValidator($dateValidator)
            ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('baselineFinishDate');
        $element->setLabel('#BaselineFinish date:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-three omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->setRequired(false)
            ->addValidator('date')
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($element);

        // the following fields will be shown just for Sys Admin users  (look at scripts/(...)/create.phtml)

        $element = new Zend_Form_Element_Text('predictedBeginDate');
        $dateValidator = new C3op_Util_ValidDate();
        $element->setLabel('#Predicted to begin:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->setRequired(false)
            ->addValidator($dateValidator)
            ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('predictedFinishDate');
        $element->setLabel('#Predicted to finish:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-three omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->setRequired(false)
            ->addValidator('date')
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('realBeginDate');
        $dateValidator = new C3op_Util_ValidDate();
        $element->setLabel('#Real Begin date:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->setRequired(false)
            ->addValidator($dateValidator)
            ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('realFinishDate');
        $element->setLabel('#Real Finish date:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-three omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->setRequired(false)
            ->addValidator('date')
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($element);
        // the fields above will be shown just for Sys Admin users (look at scripts/(...)/create.phtml)


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
            $baselineBeginDate = $this->baselineBeginDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($baselineBeginDate)) {
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($baselineBeginDate);
                $action->SetBaselineBeginDate($dateForMysql);
            }

            $baselineFinishDate = $this->baselineFinishDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($baselineFinishDate)){
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($baselineFinishDate);
                $action->SetBaselineFinishDate($dateForMysql);

            }

            $predictedBeginDate = $this->predictedBeginDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            $converter = new C3op_Util_DateConverter();
            if ($dateValidator->isValid($predictedBeginDate)) {
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedBeginDate);
                $action->SetPredictedBeginDate($dateForMysql);
            } else {
                // use baseline instead, if valid
                if ($dateValidator->isValid($baselineBeginDate)){
                    $dateForMysql = $converter->convertDateToMySQLFormat($baselineBeginDate);
                    $action->SetPredictedBeginDate($dateForMysql);
                }
            }

            $predictedFinishDate = $this->predictedFinishDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            $converter = new C3op_Util_DateConverter();
            if ($dateValidator->isValid($predictedFinishDate)){
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedFinishDate);
                $action->SetPredictedFinishDate($dateForMysql);
            } else {
                // use baseline instead, if valid
                if ($dateValidator->isValid($baselineFinishDate)){
                    $dateForMysql = $converter->convertDateToMySQLFormat($baselineFinishDate);
                    $action->SetPredictedFinishDate($dateForMysql);
                }
            }

            $realBeginDate = $this->realBeginDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($realBeginDate)) {
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($realBeginDate);
                $action->SetRealBeginDate($dateForMysql);
            }

            $realFinishDate = $this->realFinishDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($realFinishDate)){
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($realFinishDate);
                $action->SetRealFinishDate($dateForMysql);
            }




            $action->SetSubordinatedTo($this->subordinatedTo->GetValue());
            $action->setSupervisor($this->supervisor->GetValue());

//            $milestone = $this->milestone->GetValue();
//            if ($milestone == '1') {
//                $action->SetMilestone(1);
//            } else {
//                $action->SetMilestone(0);
//            }
//
//            $requirementForReceiving = intval($this->requirementForReceiving->GetValue());
//            if ($requirementForReceiving > 0) {
//                $action->SetRequirementForReceiving($requirementForReceiving);
//            } else {
//                $action->SetRequirementForReceiving(0);
//            }
//

            $actionMapper->insert($action);
            return $action->getId();
        }
    }
 }