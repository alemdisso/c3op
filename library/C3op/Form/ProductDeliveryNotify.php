<?php
class C3op_Form_ProductDeliveryNotify extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('notifyActionDeliveryForm')
            ->setAction('/projects/action/delivery-notify')
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

        $observation = new Zend_Form_Element_Textarea('observation');
        $observation->setLabel('#Observation:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setAttrib('cols','8')
            ->setAttrib('rows','5')
            ->setRequired(false)
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($observation);

//        $element = new Zend_Form_Element_Text('title');
//        $element->setLabel('#Delivery')
//                ->setDecorators(array(
//                    'ViewHelper',
//                    'Errors',
//                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
//                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
//                ))
//                ->setOptions(array('class' => 'Full alpha omega'))
//            ->addValidator(new C3op_Util_ValidString)
//            ->addFilter('StringTrim')
//                ;
//        $this->addElement($element);
//

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
            throw new C3op_Form_DeliveryCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $actionMapper = new C3op_Projects_ActionMapper($db);
            $id = $data['id'];
            $action = $actionMapper->findById($id);

            $actionFinishDate = $action->getRealFinishDate();

            $productDeliveryDate = $this->realDate->GetValue();

            $dateValidator = new C3op_Util_ValidDate();

            $dateChanged = false;
            if (($dateValidator->isValid($productDeliveryDate)) && (!is_null($productDeliveryDate)) && ($productDeliveryDate != $actionFinishDate)) {
                $dateChanged = true;
            }


            if ($dateValidator->isValid($productDeliveryDate))
            {
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($productDeliveryDate);
                $realDateConvertedToMySQL = $dateForMysql;
            }


            $observation = $this->observation->GetValue();
            if (($dateChanged) && ($observation == "")) {
                throw new C3op_Form_ResponsibleCreateException('#Date changing must be justified');
            } else {
                $notification = new C3op_Projects_DeliveryNotification();
                $notification->NotifyDelivery($action, $actionMapper, $observation);
                return ($id);
            }
        }
    }

 }