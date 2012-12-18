<?php
class C3op_Form_TeamMemberRemove extends Zend_Form
{
    protected $_buttons = array();

    public function init()
    {

        // initialize form
        $this->setName('removeTeamMemberForm')
            ->setAction('/projects/team-member/remove')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('id');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $this->setButtons(array('Submit'=>_('#Confirm removal'), 'Cancel'=>_('#Don\'t remove')));

    }

    public function process($data) {

        $db = Zend_Registry::get('db');
        $teamMemberMapper = new C3op_Projects_TeamMemberMapper($db);

        if ($this->isValid($data) !== true)
        {
            throw new C3op_Form_TeamMemberRemoveException(_('#Invalid data!'));
        }
        else
        {
            $id = $data['id'];
            $teamMember = $teamMemberMapper->findById($id);
            $teamMemberRemoval = new C3op_Projects_TeamMemberRemoval($teamMember, $teamMemberMapper);
            $teamMemberRemoval->remove();
            return $id;
        }
    }


  /**
   * Sets a list of buttons - Buttons will be standard submits, or in the getJson() version
   * they are removed from display - but stuck in the json in the .buttons property
   *
   * $buttons = array('save'=>'Save This Thing', 'cancel'=>'Cancel') as an example
   *
   * @param array $buttons
   * @return void
   * @author Corey Frang
   */
  private function setButtons($buttons)
  {
    $this->_buttons = $buttons;
    foreach ($buttons as $name => $label) {
        $this->addElement('submit', $name, array(
            'label'=>$label,
            'class'=> "submit",
            'decorators'=>array('ViewHelper'),
            ));
    }

    $this->addDisplayGroup(array_keys($this->_buttons),'buttons', array(
        'decorators'=>array(
        'FormElements',
        array('HtmlTag', array('tag' => 'div','class' => 'inset-by-nine omega')),

    )
    ));





  }




}