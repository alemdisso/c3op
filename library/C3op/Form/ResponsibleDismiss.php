<?php
class C3op_Form_ResponsibleDismiss extends Zend_Form
{

    public function init()
    {
        $this->setName('newResponsibleForm')
            ->setAction('/resources/responsible/dismiss')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $responsible = new Zend_Form_Element_Hidden('id');
        $responsible->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($responsible);

        $observation = new Zend_Form_Element_Textarea('observation');
        $observation->setLabel('Observações:')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega'))
            ->setAttrib('cols','8')
            ->setAttrib('rows','5')
            ->setRequired(false)
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($observation);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit ->setLabel('#Submit')
                ->setDecorators(array('ViewHelper','Errors',
                    array(array('data' => 'HtmlTag'),
                    array('tag' => 'div','class' => 'two columns inset-by-nine omega')),
                    array('Label',
                      array('tag' => 'div','tagClass' => 'three columns alpha Invisible')
                    ),
                  ))
                ->setOptions(array('class' => 'submit Full alpha omega'));
        $this   ->addElement($submit);
    }

    public function process($data) {
        if ($this->isValid($data) !== true)
        {
            throw new C3op_Form_ResponsibleCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $responsibleMapper = new C3op_Resources_ResponsibleMapper($db);
            $responsible = $responsibleMapper->findById($this->id->GetValue());
            $actionMapper = new C3op_Projects_ActionMapper($this->db);
            $itsAction = $actionMapper->findById($responsible->GetAction());

            $observation = $this->observation->GetValue();
            if ($observation == "") {
                throw new C3op_Form_ResponsibleCreateException('#Date changing must be justified');
            } else {
                $dismissing = new C3op_Resources_ResponsibleDismissing();
                $dismissing->responsibleDismiss($itsAction, $responsible, $responsibleMapper, $observation);

                $actionMapper->update($itsAction);
                $responsibleMapper->update($responsible);
                return $responsible->GetId();
            }
        }
    }


}