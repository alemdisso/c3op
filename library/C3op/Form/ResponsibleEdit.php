<?php
class C3op_Form_ResponsibleEdit extends C3op_Form_ResponsibleCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editResponsibleForm')
            ->setAction('/resources/responsible/edit')
            //->setAction('javascript:submitResponsibleForm();')
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('id');
        $element->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $element = new Zend_Form_Element_Text('contractedValue');
        $element->setLabel('#Contracted value:')
                ->setAttrib('alt','decimal')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns alpha omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'two columns alpha Right inset-by-two')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
            ->addValidator(new C3op_Util_ValidPositiveDecimal)
            ->addFilter('StringTrim')
            ->addErrorMessage(_('#The value must be a positive number'))
                ;
        $this->addElement($element);

        $typeField = $this->getElement('responsibleType');
        $typeField->SetOptions(array('onChange' => 'javascript:typeHasChanged()'));




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

                $institutionId = $this->institution->GetValue();

                if ($institutionId > 0) {
                    $responsible->SetInstitution($this->institution->GetValue());
                } else {
                    $responsibleMapper->delete($responsible);
                    return 0;
                }
                $linkageId = $this->linkage->GetValue();
                if ($linkageId) {
                    $linkageMapper = new C3op_Register_LinkageMapper($this->db);
                    $linkageContact = $linkageMapper->findById($linkageId);
                    $contactId = $linkageContact->GetContact();
                } else {
                    $contactId = null;
                }
                $responsible->SetContact($contactId);


                $responsible->SetType(C3op_Resources_ResponsibleTypeConstants::TYPE_OUTSIDE_SERVICE);

            } else if ($type == 'teamMember') {


                $linkageId = $this->linkage->GetValue();
                if ($linkageId > 0) {
                    $linkageMapper = new C3op_Register_LinkageMapper($this->db);
                    $linkageContact = $linkageMapper->findById($linkageId);
                    $contactId = $linkageContact->GetContact();
                    $institutionId = $linkageContact->GetInstitution();
                } else {
                    $responsibleMapper->delete($responsible);
                    return 0;
                }

                $responsible->SetInstitution($institutionId);
                $responsible->SetContact($contactId);

                $responsible->SetType(C3op_Resources_ResponsibleTypeConstants::TYPE_TEAM_MEMBER);

            }

            if (isset($this->predictedValue)) {
                $converter = new C3op_Util_DecimalConverter();
                $validator = new C3op_Util_ValidDecimal();
                if ($validator->isValid($this->predictedValue->GetValue())) {
                    $responsible->SetPredictedValue($converter->getDecimalDotValue($this->predictedValue->GetValue(), $validator));
                }
            }
            if (isset($this->contractedValue)) {
                $converter = new C3op_Util_DecimalConverter();
                $validator = new C3op_Util_ValidDecimal();
                if ($validator->isValid($this->contractedValue->GetValue())) {
                    $responsible->SetContractedValue($converter->getDecimalDotValue($this->contractedValue->GetValue(), $validator));
                }
            }

            $responsibleMapper->update($responsible);
            return $responsible->GetId();

        }
    }
 }