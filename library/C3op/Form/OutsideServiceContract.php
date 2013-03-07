<?php
class C3op_Form_OutsideServiceContract extends Zend_Form
{

    public function init()
    {
        $this->setName('newOutsideServiceForm')
            ->setAction('/resources/outside-service/contract')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $outsideService = new Zend_Form_Element_Hidden('id');
        $outsideService->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($outsideService);

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
            throw new C3op_Form_OutsideServiceCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $outsideServiceMapper = new C3op_Resources_OutsideServiceMapper($db);
            $outsideService = $outsideServiceMapper->findById($this->id->GetValue());
            $actionMapper = new C3op_Projects_ActionMapper($this->db);
            $itsAction = $actionMapper->findById($outsideService->GetAction());

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
                throw new C3op_Form_OutsideServiceCreateException('É obrigatório informar as datas contratadas.');
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
                throw new C3op_Form_OutsideServiceCreateException('#Date changing must be justified');
            } else {
                $contracting = new C3op_Resources_OutsideServiceContracting();
                $contracting->outsideServiceContract($itsAction, $outsideService, $outsideServiceMapper);
                if (($observation != "") && ($itsAction->GetPredictedBeginDate() != $newBeginDate)) {
                    C3op_Projects_ActionDateChange::ChangePredictedBeginDate($itsAction, $actionMapper, $newBeginDate, $observation);
                }
                if (($observation != "") && ($itsAction->GetPredictedFinishDate() != $newFinishDate)) {
                    C3op_Projects_ActionDateChange::ChangePredictedFinishDate($itsAction, $actionMapper, $newFinishDate, $observation);
                }

                $actionMapper->update($itsAction);
                $outsideServiceMapper->update($outsideService);
                return $outsideService->GetId();
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