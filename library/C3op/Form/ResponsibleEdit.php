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

            $type = $this->responsibleType->getValue();
            if ($type == 'service') {
                $responsible->SetInstitution($this->institution->GetValue());
                $linkageId = $this->linkage->GetValue();
                $linkageMapper = new C3op_Register_LinkageMapper($this->db);
                $linkageContact = $linkageMapper->findById($linkageId);
                $contactId = $linkageContact->GetContact();
                $responsible->SetContact($contactId);

                $converter = new C3op_Util_DecimalConverter();
                $validator = new C3op_Util_ValidDecimal();
                if ($validator->isValid($this->value->GetValue())) {
                    $responsible->SetValue($converter->getDecimalDotValue($this->value->GetValue(), $validator));
                }

                $responsible->SetType(C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE);

            } else {


                $linkageId = $this->linkage->GetValue();
                $linkageMapper = new C3op_Register_LinkageMapper($this->db);
                $linkageContact = $linkageMapper->findById($linkageId);
                $contactId = $linkageContact->GetContact();
                $institutionId = $linkageContact->GetInstitution();
                $responsible->SetInstitution($institutionId);
                $responsible->SetContact($contactId);

                $converter = new C3op_Util_DecimalConverter();
                $validator = new C3op_Util_ValidDecimal();
                if ($validator->isValid($this->value->GetValue())) {
                    $responsible->SetValue($converter->getDecimalDotValue($this->value->GetValue(), $validator));
                }

                $responsible->SetType(C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER);

            }

            $responsibleMapper->update($responsible);
            return $responsible->GetId();

        }
    }
 }