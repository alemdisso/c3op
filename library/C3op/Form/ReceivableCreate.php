<?php
class C3op_Form_ReceivableCreate extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('newReceivableForm')
            ->setAction('/projects/receivable/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $project = new Zend_Form_Element_Hidden('project');
        $project->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($project);
        $project->setDecorators(array('ViewHelper'));

        $elementText = new Zend_Form_Element_Text('title');
        $elementText->setLabel('#Receivable')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);

        // $this->addElementText('predictedDate', 'Data Prevista', new C3op_Util_ValidDate, 50);

        $elementText = new Zend_Form_Element_Text('predictedDate');
        $elementText->setLabel('#Predicted Date')
            ->setAttrib('alt','date')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);

        $elementText = new Zend_Form_Element_Text('predictedValue');
        $elementText->setLabel('#Predicted Value')
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

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit ->setLabel('#Submit')
                ->setDecorators(array('ViewHelper','Errors',
                    array(array('data' => 'HtmlTag'),
                    array('tag' => 'div','class' => 'two columns inset-by-nine omega')),
                    array('Label',
                      array('tag' => 'div','tagClass' => 'three columns alpha Invisible')
                    ),
                  ))
                ->setOptions(array('class' => 'submit Full alpha omega'));
        $this   ->addElement($submit);
    }

    public function process($data) {

        if ($this->isValid($data) !== true)
        {
            throw new C3op_Form_ReceivableCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $receivableMapper = new C3op_Projects_ReceivableMapper($db);

            $predictedDate = $this->predictedDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedDate))
            {
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedDate);
                $predictedDateConvertedToMySQL = $dateForMysql;
            }

            $predictedValueWithDecimalPoint = $this->prepareCurrencyValueToSet($this->predictedValue->GetValue(), new C3op_Util_ValidFloat(), new C3op_Util_FloatConverter());


            $receivable = new C3op_Projects_Receivable($this->project->GetValue(),$predictedDateConvertedToMySQL, $predictedValueWithDecimalPoint);
            $receivable->SetTitle($this->title->GetValue());
            $receivable->SetProject((float)$this->project->GetValue());
            $receivable->SetRealDate($realDateConvertedToMySQL);

            $receivableMapper->insert($receivable);
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

    private function prepareCurrencyValueToSet($value, C3op_Util_ValidFloat $validator, C3op_Util_FloatConverter $converter)
    {
        if ($validator->isValid($value)) {
            if ($converter->identifyDecimalComma($value)) {
                return $converter->convertDecimalCommaToDecimalDot($value);
            } else {
                return value;
            }
        } else {
            throw new C3op_Projects_ReceivableException("Invalid value");
        }
    }



 }