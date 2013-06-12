<?php
class C3op_Form_ReceivableCreate extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('newReceivableForm')
            ->setAction('/finances/receivable/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('project');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $element = new Zend_Form_Element_Text('title');
        $element->setLabel('#Receivable')
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
                ->setOptions(array('class' => 'eleven columns alpha omega'))
                ->setRequired(false)
                ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('deliveryDate');
        $element->setLabel('#Delivery Date')
            ->setAttrib('alt','date')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('predictedDate');
        $element->setLabel('#receivable.form.Predicted Date')
            ->setAttrib('alt','date')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('predictedValue');
        $element->setLabel('#Predicted Value')
            ->setAttrib('alt','decimal')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'two columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);

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
            $receivableMapper = new C3op_Finances_ReceivableMapper($db);

            $dateValidator = new C3op_Util_ValidDate();
            $converter = new C3op_Util_DateConverter();

            $deliveryDate = $this->deliveryDate->GetValue();
            if ($dateValidator->isValid($deliveryDate))
            {
                $dateForMysql = $converter->convertDateToMySQLFormat($deliveryDate);
                $deliveryDateConvertedToMySQL = $dateForMysql;
            }

            $predictedDate = $this->predictedDate->GetValue();
            if ($dateValidator->isValid($predictedDate))
            {
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedDate);
                $predictedDateConvertedToMySQL = $dateForMysql;
            }

            $converter = new C3op_Util_DecimalConverter();
            $predictedValueWithDecimalPoint = $converter->getDecimalDotValue($this->predictedValue->GetValue(), new C3op_Util_ValidDecimal());


            $receivable = new C3op_Finances_Receivable($this->project->GetValue(),$predictedDateConvertedToMySQL, $predictedValueWithDecimalPoint);
            $receivable->SetProject((float)$this->project->GetValue());
            $receivable->SetTitle($this->title->GetValue());
            $receivable->SetDescription($this->description->GetValue());
            $receivable->SetRealDate($realDateConvertedToMySQL);

            $receivableMapper->insert($receivable);

            $deliveryMapper = new C3op_Projects_DeliveryMapper($db);

            $delivery = new C3op_Projects_Delivery($receivable->getId(), $receivable->GetProject(), $deliveryDateConvertedToMySQL);
            $deliveryMapper->insert($delivery);


            return $receivable->getId();

        }
    }

 }