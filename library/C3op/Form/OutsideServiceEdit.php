<?php
class C3op_Form_OutsideServiceEdit extends C3op_Form_OutsideServiceCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editOutsideServiceForm')
            ->setAction('/projects/outside-service/edit')
            ->setMethod('post');

        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($id);



    }

    public function process($data) {

        $db = Zend_Registry::get('db');
        $outsideServiceMapper = new C3op_Projects_OutsideServiceMapper($db);

        if ($this->isValid($data) !== true) {
            throw new C3op_Form_InstitutionEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $outsideService = $outsideServiceMapper->findById($id);
            $outsideService->SetDescription($this->description->GetValue());
            $outsideService->SetInstitution($this->institution->GetValue());
            $outsideService->SetLinkage($this->linkage->GetValue());

            $converter = new C3op_Util_DecimalConverter();
            $validator = new C3op_Util_ValidDecimal();
            if ($validator->isValid($this->value->GetValue())) {
                $outsideService->SetValue($converter->getDecimalDotValue($this->value->GetValue(), $validator));
            }

            $outsideService->SetAction($this->action->GetValue());
            $outsideServiceMapper->update($outsideService);
            return $outsideService->GetId();
        }
    }
 }