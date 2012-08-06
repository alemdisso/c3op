<?php
class C3op_Form_HumanResourceContract extends Zend_Form
{
    
    public function init()
    {
        $this->setName('newHumanResourceForm')
            ->setAction('/projects/human-resource/contract')
            ->setMethod('post');
        
        $humanResource = new Zend_Form_Element_Hidden('id');
        $humanResource->addValidator('Int')
            ->addFilter('StringTrim');        
        $this->addElement($humanResource);
        
        $predictedBeginDate = new Zend_Form_Element_Text('predictedBeginDate');
        $dateValidator = new C3op_Util_ValidDate();
        $predictedBeginDate->setLabel('Data de início:')
            ->setOptions(array('size' => '35'))
            ->setRequired(true)
            ->addValidator($dateValidator)
            ->addFilter('StringTrim');
        $this->addElement($predictedBeginDate);
        
        $predictedFinishDate = new Zend_Form_Element_Text('predictedFinishDate');
        $predictedFinishDate->setLabel('Data de término:')
            ->setOptions(array('size' => '35'))
            ->setRequired(true)
            ->addValidator('date')
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($predictedFinishDate);
        
        $observation = new Zend_Form_Element_Textarea('observation');
        $observation->setLabel('Observações:')
            ->setAttrib('cols','8')
            ->setAttrib('rows','5')
            ->setRequired(false)
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($observation);
        
        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Salvar')
            ->setOptions(array('class' => 'submit'));
        $this->addElement($submit);
                
    }
    
    public function process($data) {
        if ($this->isValid($data) !== true) 
        {
            throw new C3op_Form_HumanResourceCreateException('Invalid data!');
        } 
        else
        {
            $db = Zend_Registry::get('db');
            $humanResourceMapper = new C3op_Projects_HumanResourceMapper($db);
            $humanResource = $humanResourceMapper->findById($this->id->GetValue());
            $actionMapper = new C3op_Projects_ActionMapper($this->db);
            $itsAction = $actionMapper->findById($humanResource->GetAction());
            
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
                throw new C3op_Form_HumanResourceCreateException('É obrigatório informar as datas contratadas.');
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
                throw new C3op_Form_HumanResourceCreateException('Mudanças de data devem ser justificadas.');
            } else {
                C3op_Projects_HumanResourceContracting::ContactContract($itsAction, $humanResource, $humanResourceMapper);
                if (($observation != "") && ($itsAction->GetPredictedBeginDate() != $newBeginDate)) {
                    C3op_Projects_ActionDateChange::ChangePredictedBeginDate($itsAction, $actionMapper, $newBeginDate, $observation);
                }
                if (($observation != "") && ($itsAction->GetPredictedFinishDate() != $newFinishDate)) {
                    C3op_Projects_ActionDateChange::ChangePredictedFinishDate($itsAction, $actionMapper, $newFinishDate, $observation);
                }

                $actionMapper->update($itsAction);
                $humanResourceMapper->update($humanResource);
                return $humanResource->GetId();
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