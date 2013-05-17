<?php
class C3op_Form_ResponsibleContract extends Zend_Form
{

    public function init()
    {
        $this->setName('newResponsibleForm')
            ->setAction('/resources/responsible/contract')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $responsible = new Zend_Form_Element_Hidden('id');
        $responsible->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($responsible);

        $predictedBeginDate = new Zend_Form_Element_Text('predictedBeginDate');
        $dateValidator = new C3op_Util_ValidDate();
        $predictedBeginDate->setLabel('#Begin date:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->setRequired(true)
            ->addValidator($dateValidator)
            ->addFilter('StringTrim');
        $this->addElement($predictedBeginDate);

        $predictedFinishDate = new Zend_Form_Element_Text('predictedFinishDate');
        $predictedFinishDate->setLabel('#Finish date:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-two omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->setRequired(true)
            ->addValidator('date')
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($predictedFinishDate);

        $element = new Zend_Form_Element_Text('value');
        $element->setLabel('#Value:')
                ->setAttrib('alt','decimal')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns alpha omega inset-by-eight')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidPositiveDecimal)
            ->addFilter('StringTrim')
            ->addErrorMessage(_('#The value must be a positive number'))
                ;
        $this->addElement($element);


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
            throw new C3op_Form_ResponsibleCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $responsibleMapper = new C3op_Resources_ResponsibleMapper($db);
            $responsible = $responsibleMapper->findById($this->id->GetValue());
            $actionMapper = new C3op_Projects_ActionMapper($this->db);
            $itsAction = $actionMapper->findById($responsible->GetAction());

            $weHaveThedates = true;
            $predictedBeginDate = $this->predictedBeginDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();

            if ($dateValidator->isValid($predictedBeginDate)) {
                $converter = new C3op_Util_DateConverter();
                $newBeginDate = $converter->convertDateToMySQLFormat($predictedBeginDate);

            } else {
                $weHaveThedates = false;
            }

            $predictedFinishDate = $this->predictedFinishDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedFinishDate)){
                $converter = new C3op_Util_DateConverter();
                $newFinishDate = $converter->convertDateToMySQLFormat($predictedFinishDate);
            } else {
                $weHaveThedates = false;
            }

            if (!$weHaveThedates) {
                throw new C3op_Form_ResponsibleCreateException('É obrigatório informar as datas contratadas.');
            }

            $formerPredictedBeginDate = $itsAction->GetPredictedBeginDate();
            $formerPredictedFinishDate = $itsAction->GetPredictedFinishDate();

            $dateChanged = false;
            if (($dateValidator->isValid($formerPredictedBeginDate)) && (!is_null($formerPredictedBeginDate)) && ($formerPredictedBeginDate != $newBeginDate)) {
                $dateChanged = true;
            }
            if (($dateValidator->isValid($formerPredictedFinishDate)) && (!is_null($formerPredictedFinishDate)) && ($formerPredictedFinishDate != $newFinishDate)) {
                $dateChanged = true;
            }

            $valueChanged = false;
            $contractedValue = $this->value->GetValue();
            $converter = new C3op_Util_DecimalConverter();
            $validator = new C3op_Util_ValidDecimal();
            if ($validator->isValid($contractedValue)) {
                $convertedValue = $converter->getDecimalDotValue($contractedValue, $validator);
            } else {
                throw new C3op_Form_ResponsibleCreateException('#Invalid value for contracting');
            }




            if ($convertedValue != $responsible->getValue()) {
                $responsible->setValue($contractedValue);
                $valueChanged = true;
            }

            $observation = $this->observation->GetValue();
            if (($dateChanged || $valueChanged) && ($observation == "")) {
                if ($dateChanged) {
                    throw new C3op_Form_ResponsibleCreateException('#Date changing must be justified');
                }
                if ($valueChanged) {
                    throw new C3op_Form_ResponsibleCreateException('#Value changing must be justified');
                }
            } else {
                $contracting = new C3op_Resources_ResponsibleContracting();
                $contracting->responsibleContract($itsAction, $responsible, $responsibleMapper);
                if ($itsAction->GetPredictedBeginDate() != $newBeginDate) {
                    $dataChanger = new C3op_Projects_ActionDateChange($itsAction, $actionMapper);
                    $dataChanger->ChangePredictedBeginDate($newBeginDate, $observation);
                }
                if ($itsAction->GetPredictedFinishDate() != $newFinishDate) {
                    $dataChanger = new C3op_Projects_ActionDateChange($itsAction, $actionMapper);
                    $dataChanger->ChangePredictedFinishDate($newBeginDate, $observation);
                }

                $actionMapper->update($itsAction);
                $responsibleMapper->update($responsible);
                return $responsible->GetId();
            }
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