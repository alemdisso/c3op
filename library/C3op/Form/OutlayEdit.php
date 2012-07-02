<?php
class C3op_Form_OutlayEdit extends C3op_Form_OutlayCreate
{
    
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editOutlayForm')
            ->setAction('/projects/outlay/edit')
            ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($id);
        

    }
    
    public function process($data) {
        
        $db = Zend_Registry::get('db');
        $outlayMapper = new C3op_Projects_OutlayMapper($db);        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_OutlayEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $outlay = $outlayMapper->findById($id);      
            $outlay->SetAction($data['action']);
            $outlay->SetProject($data['project']);
            $outlay->SetHumanResource($data['humanResource']);
            $outlay->SetPredictedValue($data['predictedValue']);
            $outlay->SetPredictedDate($this->prepareDateValueToSet($data['predictedDate'], new C3op_Util_ValidDate(), new C3op_Util_DateConverter()));
            $outlay->SetObservation($data['observation']);

            $outlayMapper->update($outlay);
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
    
    private function prepareDateValueToSet($value, C3op_Util_ValidDate $validator, C3op_Util_DateConverter $converter)
    {
        if ($validator->isValid($value)) {
            return $converter->convertDateToMySQLFormat($value);
        } else {
            return "";
        }
    }
    
    
}