<?php
class C3op_Form_ReceivingEdit extends C3op_Form_ReceivingCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editReceivingForm')
            ->setAction('/projects/receiving/edit')
            ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        
                        

    }
    
    public function process($data) {
        
        $db = Zend_Registry::get('db');
        $receivingMapper = new C3op_Projects_ReceivingMapper($db);        
        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ReceivingEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $receiving = $receivingMapper->findById($id);      
            $receiving->SetTitle($data['title']);
            $receiving->SetProject($data['project']);
            $receiving->SetPredictedDate($this->prepareDateValueToSet($data['predictedDate'], new C3op_Util_ValidDate(), new C3op_Util_DateConverter()));
            $receiving->SetPredictedValue($data['predictedValue']);
            $receiving->SetRealDate($this->prepareDateValueToSet($data['realDate'], new C3op_Util_ValidDate(), new C3op_Util_DateConverter()));
            $receiving->SetRealValue($data['realValue']);
            $receivingMapper->update($receiving);
        }
    }

    private function prepareDateValueToSet($value, C3op_Util_ValidDate $validator, C3op_Util_DateConverter $converter)
    {
        if ($validator->isValid($value)) {
            return $converter->convertDateToMySQLFormat($value);
        } else {
            return "";
        }
    }
    
}