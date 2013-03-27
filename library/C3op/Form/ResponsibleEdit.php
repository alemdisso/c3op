<?php
class C3op_Form_ResponsibleEdit extends C3op_Form_ResponsibleCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editResponsibleForm')
            ->setAction('/resources/responsible/edit')
            ->setMethod('post');

        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($id);



    }

    public function process($data) {

        $db = Zend_Registry::get('db');
        $responsibleMapper = new C3op_Resources_ResponsibleMapper($db);

        if ($this->isValid($data) !== true) {
            throw new C3op_Form_InstitutionEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $responsible = $responsibleMapper->findById($id);
            $responsible->SetDescription($this->description->GetValue());
            $responsible->SetLinkage($this->linkage->GetValue());

            $converter = new C3op_Util_DecimalConverter();
            $validator = new C3op_Util_ValidDecimal();
            if ($validator->isValid($this->value->GetValue())) {
                $responsible->SetValue($converter->getDecimalDotValue($this->value->GetValue(), $validator));
            }

            $responsible->SetAction($this->action->GetValue());
            $responsibleMapper->update($responsible);
            return $responsible->GetId();
        }
    }
 }