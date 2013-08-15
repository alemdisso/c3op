<?php
class C3op_Form_ProductLeanCreate extends C3op_Form_ProductCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editActionForm')
            ->setAction('/projects/product/lean-create')
            //->setAction('javascript:submitLeanCreateForm();')
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('id');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $this->removeElement('milestone');
        $this->removeElement('requirementForReceiving');
        $this->removeElement('subordinatedTo');
        $this->removeElement('supervisor');
        $this->removeElement('description');
        $this->removeElement('predictedBeginDate');
        $this->removeElement('predictedFinishDate');


    }

    public function process($data) {

        $db = Zend_Registry::get('db');
        $actionMapper = new C3op_Projects_ActionMapper($db);

        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ActionEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $action = $actionMapper->findById($id);
            $action->SetTitle($data['title']);
            $action->SetProject($data['project']);
            $action->SetProduct($data['product']);

            $actionMapper->update($action);
            return $id;
        }
    }
 }