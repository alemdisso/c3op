<?php
class C3op_Form_ProductLeanCreate extends C3op_Form_ProductCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('newProductForm')
            ->setAction('/projects/product/lean-create')
            //->setAction('javascript:callProductLeanCreate();')
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('id');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $this->removeElement('milestone');
        $this->removeElement('subordinatedTo');
        $this->removeElement('supervisor');
        $this->removeElement('description');
        $this->removeElement('predictedBeginDate');
        $this->removeElement('predictedFinishDate');
        $this->removeElement('requirementForReceiving');
        $element = new Zend_Form_Element_Hidden('requirementForReceiving');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));






    }

    public function process($data) {

        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ActionCreateException('Invalid data!');
        } else {
            $db = Zend_Registry::get('db');
            $actionMapper = new C3op_Projects_ActionMapper($db);

            $action = new C3op_Projects_Action($this->project->GetValue());

            $action->SetTitle($data['title']);

            $action->SetStatus(C3op_Projects_ActionStatusConstants::STATUS_PLAN);
            $action->SetProduct(true);
            $requirementForReceiving = intval($this->requirementForReceiving->GetValue());
            if ($requirementForReceiving > 0) {
                $action->SetRequirementForReceiving($requirementForReceiving);
            } else {
                $action->SetRequirementForReceiving(0);
            }

            $actionMapper->insert($action);
            return $action->getId();
        }
    }
 }