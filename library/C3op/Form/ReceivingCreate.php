<?php
class C3op_Form_ReceivingCreate extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('newReceivingForm')
            ->setAction('/projects/receiving/create')
            ->setMethod('post');
        
        $project = new Zend_Form_Element_Hidden('project');
        $project->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($project);

        $this->addElementText('name', 'recebimento', new C3op_Util_ValidString, 50);
        $this->addElementText('predictedDate', 'Data Prevista', new C3op_Util_ValidDate, 50);
        $this->addElementText('realDate', 'Data Realizada', new C3op_Util_ValidDate, 50);
        $this->addElementText('predictedValue', 'Valor Previsto', new C3op_Util_PositiveValidFloat, 50);
        $this->addElementText('realValue', 'Valor Realizado', new C3op_Util_PositiveValidFloat, 50);
        
        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Salvar')
            ->setOptions(array('class' => 'submit'));
        $this->addElement($submit);
                

    }
    
    public function process($data) {
        
        
        if ($this->isValid($data) !== true) 
        {
            throw new C3op_Form_ReceivingCreateException('Invalid data!');
        } 
        else
        {
            $db = Zend_Registry::get('db');
            $receivingMapper = new C3op_Projects_ReceivingMapper($db);
            
            $predictedDate = $this->predictedDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedDate))
            {
                $converter = new C3op_Util_DateConverter();                
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedDate);
                $predictedDateConvertedToMySQL = $dateForMysql;
            }
            
            $realDate = $this->realDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($realDate))
            {
                $converter = new C3op_Util_DateConverter();                
                $dateForMysql = $converter->convertDateToMySQLFormat($realDate);
                $realDateConvertedToMySQL = $dateForMysql;
                $receiving->SetRealDate($realDateConvertedToMySQL);
            }
            
            
            
            $receiving = new C3op_Projects_Receiving($this->project->GetValue(),$predictedDateConvertedToMySQL, $this->predictedValue->GetValue());
            $receiving->SetName($this->name->GetValue());
            $receiving->SetProject((float)$this->project->GetValue());
            $receiving->SetRealValue((float)$this->realValue->GetValue());
            
            $receivingMapper->insert($receiving);
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