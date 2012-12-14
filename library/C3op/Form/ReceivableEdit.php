<?php
class C3op_Form_ReceivableEdit extends C3op_Form_ReceivableCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editReceivableForm')
            ->setAction('/projects/receivable/edit')
            ->setMethod('post');

        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($id);



    }

    public function process($data) {

        $db = Zend_Registry::get('db');
        $receivableMapper = new C3op_Projects_ReceivableMapper($db);

        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ReceivableEditException('Invalid data!');
        } else {

            $id = $data['id'];
            $receivable = $receivableMapper->findById($id);
            $receivable->SetTitle($data['title']);
            $receivable->SetDescription($data['description']);
            $receivable->SetProject($data['project']);
            $receivable->SetPredictedDate($this->prepareDateValueToSet($data['predictedDate'], new C3op_Util_ValidDate(), new C3op_Util_DateConverter()));
            $converter = new C3op_Util_FloatConverter();
            $receivable->SetPredictedValue($converter->getDecimalDotValue($data['predictedValue'], new C3op_Util_ValidFloat()));
            $receivableMapper->update($receivable);
            return $id;
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