<?php
class C3op_Form_MaterialSupplyContract extends Zend_Form
{

    public function init()
    {
        $this->setName('newMaterialSupplyForm')
            ->setAction('/resources/material-supply/contract')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $materialSupply = new Zend_Form_Element_Hidden('id');
        $materialSupply->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($materialSupply);

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

        $observation = new Zend_Form_Element_Textarea('observation');
        $observation->setLabel('Observações:')
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
            throw new C3op_Form_MaterialSupplyCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $materialSupplyMapper = new C3op_Resources_MaterialSupplyMapper($db);
            $materialSupply = $materialSupplyMapper->findById($this->id->GetValue());
            $actionMapper = new C3op_Projects_ActionMapper($this->db);
            $itsAction = $actionMapper->findById($materialSupply->GetAction());

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
                throw new C3op_Form_MaterialSupplyCreateException('É obrigatório informar as datas contratadas.');
            }

            $formerPredictedBeginDate = $itsAction->GetPredictedBeginDate();
            $formerPredictedFinishDate = $itsAction->GetPredictedFinishDate();

            $dateChanged = false;
            if (($dateValidator->isValid($formerPredictedBeginDate)) && ($formerPredictedBeginDate != $newBeginDate)) {
                $dateChanged = true;
            }
            if (($dateValidator->isValid($formerPredictedFinishDate)) && ($formerPredictedFinishDate != $newFinishDate)) {
                $dateChanged = true;
            }

            $observation = $this->observation->GetValue();
            if ($dateChanged && ($observation == "")) {
                throw new C3op_Form_MaterialSupplyCreateException('#Date changing must be justified');
            } else {
                $contracting = new C3op_Resources_MaterialSupplyContracting();
                $contracting->materialSupplyContract($itsAction, $materialSupply, $materialSupplyMapper);
                if (($observation != "") && ($itsAction->GetPredictedBeginDate() != $newBeginDate)) {
                    C3op_Projects_ActionDateChange::ChangePredictedBeginDate($itsAction, $actionMapper, $newBeginDate, $observation);
                }
                if (($observation != "") && ($itsAction->GetPredictedFinishDate() != $newFinishDate)) {
                    C3op_Projects_ActionDateChange::ChangePredictedFinishDate($itsAction, $actionMapper, $newFinishDate, $observation);
                }

                $actionMapper->update($itsAction);
                $materialSupplyMapper->update($materialSupply);
                return $materialSupply->GetId();
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