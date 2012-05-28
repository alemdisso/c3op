<?php

class ProjectCreateTest extends ControllerTestCase
{
    
    private $projectMapper;
    private $projectsBeforeTests;
    
    public function setUp() 
    {
        parent::setUp();
        $db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($db);
        
        $this->projectsBeforeTests = $this->projectMapper->getAllIds();
         
    }

    public function tearDown() 
    {
        $projectsAfterTests = $this->projectMapper->getAllIds();
        foreach($projectsAfterTests as $id) {
            if (!in_array($id, $this->projectsBeforeTests)) {
                $project = $this->projectMapper->findById($id);
                $this->projectMapper->delete($project);
            }
        }
        parent::tearDown();
        
    }
    
    public function testCanCreateFormToCreateProject()
    {
        $form = new C3op_Form_ProjectCreate;
        $this->assertEquals('C3op_Form_ProjectCreate', get_class($form));
    }
    
    public function testCanFindAllElements()
    {
        $form = new C3op_Form_ProjectCreate;
        $this->assertEquals('Zend_Form_Element_Text', get_class($form->getElement('title')));
        $this->assertEquals('Zend_Form_Element_Text', get_class($form->getElement('dateBegin')));
        $this->assertEquals('Zend_Form_Element_Text', get_class($form->getElement('date_finish')));
        $this->assertEquals('Zend_Form_Element_Text', get_class($form->getElement('value')));
        $this->assertEquals('Zend_Form_Element_Text', get_class($form->getElement('overhead')));
        $this->assertEquals('Zend_Form_Element_Text', get_class($form->getElement('management_fee')));
        $this->assertEquals('Zend_Form_Element_Textarea', get_class($form->getElement('object')));
        $this->assertEquals('Zend_Form_Element_Textarea', get_class($form->getElement('summary')));
        $this->assertEquals('Zend_Form_Element_Textarea', get_class($form->getElement('observation')));
        $this->assertEquals('Zend_Form_Element_Submit', get_class($form->getElement('submit')));
    }
    
    public function testUserTriesToCreateEmptyProjectAndFail() {
        
       $form = new C3op_Form_ProjectCreate();
       $formData = array(
           'title' => '',
           'submit' => true
           );
        $this->setExpectedException('C3op_Form_ProjectCreateException');
        $form->process($formData);
    }
    
    public function testUserTriesToCreateProjectWithWeirdDateAndFail() {
        
       $form = new C3op_Form_ProjectCreate();
       $formData = array(
           'title' => 'weird one',
           'dateBegin' => '20-77-10',
           'submit' => true
           );
        $this->setExpectedException('C3op_Form_ProjectCreateException');
        $form->process($formData);
        
    }

    public function testUserTriesToCreateProjectWithNegativeValueAndFail() {
        
       $form = new C3op_Form_ProjectCreate();
       $formData = array(
           'title' => 'aaa',
           'dateBegin' => '20-7-2010',
           'value' => '-1',
           'submit' => true
           );
        $this->setExpectedException('C3op_Form_ProjectCreateException');
        $form->process($formData);
    }
    
    /*
     *  
     */
    public function testUserCanUseAccentedCharacters() {
        
       $form = new C3op_Form_ProjectCreate();
       $formData = array(
           //'title' => 'we scream to weird chars like ã é or ç?!?!?...;- yeah!',
           'title' => 'we scream to weird chars like ã é or ç?!?!?...;- yeah!',
           'submit' => true
           );
        $form->process($formData);
    }
    
     
}