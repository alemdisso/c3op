<?php


class C3op_Util_SetButtons
{

        protected $_buttons = array();


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
  public function setButtons(Zend_Form $form, $buttons)
  {
    $this->_buttons = $buttons;
    foreach ($buttons as $name => $label) {
        $form->addElement('submit', $name, array(
            'label'=>$label,
            'class'=> "submit",
            'decorators'=>array('ViewHelper'),
            ));
    }

    $form->addDisplayGroup(array_keys($this->_buttons),'buttons', array(
        'decorators'=>array(
        'FormElements',
        array('HtmlTag', array('tag' => 'div','class' => 'inset-by-nine omega')),

    )
    ));

  }


}


?>