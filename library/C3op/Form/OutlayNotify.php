<?php
class C3op_Form_OutlayNotify extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('notifyOutlayForm')
            ->setAction('/finances/outlay/notify')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('project');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $element = new Zend_Form_Element_Hidden('id');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));


        $element = new Zend_Form_Element_Text('realDate');
        $element->setLabel('#Real Date')
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
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('realValue');
        $element->setLabel('#Real Value')
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
            throw new C3op_Form_OutlayCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $outlayMapper = new C3op_Finances_OutlayMapper($db);
            $id = $data['id'];


            $realDate = $this->realDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($realDate))
            {
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($realDate);
                $realDateConvertedToMySQL = $dateForMysql;
            }
            $converter = new C3op_Util_DecimalConverter();
            $realValueWithDecimalPoint = $converter->getDecimalDotValue($this->realValue->GetValue(), new C3op_Util_ValidDecimal());


            $outlay = $outlayMapper->findById($id);
            $outlay->SetRealDate($realDateConvertedToMySQL);
            $outlay->SetRealValue($realValueWithDecimalPoint);

            $outlayMapper->update($outlay);

            return($outlay->getId());
        }
    }

 }