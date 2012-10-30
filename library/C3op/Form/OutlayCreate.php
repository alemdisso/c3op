<?php
class C3op_Form_OutlayCreate extends Zend_Form
{
    public function init()
    {
        $this->setName('newOutlayForm')
             ->setAction('/projects/outlay/create')
             ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
             ->setMethod('post');

        $teamMember = new Zend_Form_Element_Hidden('teamMember');
        $teamMember->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($teamMember);

        $action = new Zend_Form_Element_Hidden('action');
        $action->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($action);

        $project = new Zend_Form_Element_Hidden('project');
        $project->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($project);

        // $this->addElementText('predictedValue', 'Valor:', new C3op_Util_ValidPositiveFloat(), 50);

        $elementText = new Zend_Form_Element_Text('predictedValue');
        $elementText->setLabel('#Value:')
            ->setAttrib('alt','decimal')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);

        // $this->addElementText('predictedDate', 'Data:', new C3op_Util_ValidDate(), 50);

        $elementText = new Zend_Form_Element_Text('predictedDate');
        $elementText->setLabel('#Predicted Date')
            ->setAttrib('alt','date')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-two omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);




        $observation = new Zend_Form_Element_Textarea('observation');
        $observation->setLabel('Observações:')
            ->setAttrib('cols','8')
            ->setAttrib('rows','5')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns Right alpha')),
            ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setRequired(false)
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($observation);


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
            throw new C3op_Form_OutlayCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');

            $outlayMapper = new C3op_Projects_OutlayMapper($db);

            $outlay = new C3op_Projects_Outlay($this->teamMember->GetValue());
            $outlay->SetAction($this->action->GetValue());
            $outlay->SetProject($this->project->GetValue());
            $outlay->SetPredictedValue($this->predictedValue->GetValue());
            $predictedDate = $this->predictedDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedDate)){
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedDate);
                $outlay->SetPredictedDate($dateForMysql);
            }
            $outlay->SetObservation($this->observation->GetValue());


            $outlayMapper->insert($outlay);
        }
    }

    private function addElementText($fieldName, $label, $validator, $fieldSize)
    {
        $elementText = new Zend_Form_Element_Text($fieldName);
        $elementText->setLabel($label)
            ->setOptions(array('size' => "$fieldSize"))
            ->addValidator($validator)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);

    }


}