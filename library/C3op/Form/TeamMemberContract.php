<?php
class C3op_Form_TeamMemberContract extends Zend_Form
{
    
    public function init()
    {
        $this->setName('newTeamMemberForm')
            ->setAction('/projects/team-member/contract')
            ->setMethod('post');
        
        $teamMember = new Zend_Form_Element_Hidden('id');
        $teamMember->addValidator('Int')
            ->addFilter('StringTrim');        
        $this->addElement($teamMember);
        
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
            throw new C3op_Form_TeamMemberCreateException('Invalid data!');
        } 
        else
        {
            $db = Zend_Registry::get('db');
            $teamMemberMapper = new C3op_Projects_TeamMemberMapper($db);
            $teamMember = $teamMemberMapper->findById($this->id->GetValue());
            $actionMapper = new C3op_Projects_ActionMapper($this->db);
            $itsAction = $actionMapper->findById($teamMember->GetAction());
            
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
                throw new C3op_Form_TeamMemberCreateException('É obrigatório informar as datas contratadas.');
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
                throw new C3op_Form_TeamMemberCreateException('Mudanças de data devem ser justificadas.');
            } else {
                C3op_Projects_TeamMemberContracting::ContactContract($itsAction, $teamMember, $teamMemberMapper);
                if (($observation != "") && ($itsAction->GetPredictedBeginDate() != $newBeginDate)) {
                    C3op_Projects_ActionDateChange::ChangePredictedBeginDate($itsAction, $actionMapper, $newBeginDate, $observation);
                }
                if (($observation != "") && ($itsAction->GetPredictedFinishDate() != $newFinishDate)) {
                    C3op_Projects_ActionDateChange::ChangePredictedFinishDate($itsAction, $actionMapper, $newFinishDate, $observation);
                }

                $actionMapper->update($itsAction);
                $teamMemberMapper->update($teamMember);
                return $teamMember->GetId();
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