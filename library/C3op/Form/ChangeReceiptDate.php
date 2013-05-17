<?php
class C3op_Form_ChangeReceiptDate extends Zend_Form
{

    public function init()
    {
        $this->setName('changeReceiptDateForm')
            ->setAction('/projects/action/change-receipt')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('id');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $element = new Zend_Form_Element_Hidden('project');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $element = new Zend_Form_Element_Text('newReceiptDate');
        $dateValidator = new C3op_Util_ValidDate();
        $element->setLabel('#Receipt date:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-six')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->setRequired(true)
            ->addValidator($dateValidator)
            ->addFilter('StringTrim');
        $this->addElement($element);

        $element = new Zend_Form_Element_Textarea('observation');
        $element->setLabel('#Observation:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eight columns omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setAttrib('cols','8')
            ->setAttrib('rows','5')
            ->setRequired(false)
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
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
        $db = Zend_Registry::get('db');
        $actionMapper = new C3op_Projects_ActionMapper($db);

        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ActionEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $action = $actionMapper->findById($id);
            $observation = $this->observation->GetValue();

            $dateValidator = new C3op_Util_ValidDate();

            $newReceiptDate = $this->newReceiptDate->GetValue();
            if ($dateValidator->isValid($newReceiptDate)) {
                $converter = new C3op_Util_DateConverter();
                $newReceiptDate = $converter->convertDateToMySQLFormat($newReceiptDate);
            }

            $dateChanger = new C3op_Projects_ActionDateChange($action, $actionMapper);

            $compare = new C3op_Util_DateCompare();
            $timeNewReceiptDate = strtotime($newReceiptDate);

            if ($compare->isPast($timeNewReceiptDate)) {

                //altera data de recebimento
                $acknowledgment = new C3op_Projects_ReceiptAcknowledgment();
                $acknowledgment->changeReceiptDate($action, $actionMapper, $newReceiptDate, $observation);

            } else {
            }

            return $action->GetId();


        }
    }

}