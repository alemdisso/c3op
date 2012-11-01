<?php
class C3op_Form_OutlayCreate extends Zend_Form
{
    public function init()
    {
        $this->setName('newOutlayForm')
             ->setAction('/projects/outlay/create')
             ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
             ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('teamMember');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $element = new Zend_Form_Element_Hidden('action');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $element = new Zend_Form_Element_Hidden('project');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $element = new Zend_Form_Element_Text('predictedValue');
        $element->setLabel('#Value:')
            ->setAttrib('alt','decimal')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidFloat())
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('predictedDate');
        $element->setLabel('#Predicted Date')
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
        $this->addElement($element);


        $element = new Zend_Form_Element_Textarea('observation');
        $element->setLabel('#Observation:')
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
            throw new C3op_Form_OutlayCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');

            $outlayMapper = new C3op_Projects_OutlayMapper($db);

            $outlay = new C3op_Projects_Outlay($this->teamMember->GetValue());
            $outlay->SetAction($this->action->GetValue());
            $outlay->SetProject($this->project->GetValue());

            $converter = new C3op_Util_FloatConverter();

            $outlay->SetPredictedValue($converter->getDecimalDotValue($this->predictedValue->GetValue(), new C3op_Util_ValidFloat()));
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

}