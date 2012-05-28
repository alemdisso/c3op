<?php

class ProjectEditTest extends ControllerTestCase
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
    
    public function testCanCreateFormToEditProject()
    {
        $form = new C3op_Form_ProjectEdit;
        $this->assertEquals('C3op_Form_ProjectEdit', get_class($form));
    }
    
    public function testCanFindAllElements()
    {
        $form = new C3op_Form_ProjectEdit;
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
    
    public function testUserTriesToEmptyProjectTitleAndFail() {
        
       $form = new C3op_Form_ProjectEdit();
       $formData = array(
           'title' => '',
           'submit' => true
           );
        $this->setExpectedException('C3op_Form_ProjectEditException');
        $form->process($formData);
    }
    
    public function testUserTriesToChangeDateToAWeirdOneAndFail() {
        
       $project = new C3op_Projects_Project();
       $project->SetTitle("when? not sure...");
       $this->projectMapper->insert($project);
       $id = $project->GetId();
        
       $form = new C3op_Form_ProjectEdit();
       $formData = array(
           'id' => $id,
           'title' => 'weird one again',
           'dateBegin' => '20-77-10',
           'submit' => true
           );
        $this->setExpectedException('C3op_Form_ProjectEditException');
        $form->process($formData);
       
    }

    public function testUserTriesToSetANegativeValueToProjectAndFail() {
        
        $project = new C3op_Projects_Project();
        $project->SetTitle("some title for a bad project");
        $this->projectMapper->insert($project);
        $id = $project->GetId();

        $form = new C3op_Form_ProjectEdit();
        $formData = array(
            'id' => $id,
            'title' => 'too bad',
            'dateBegin' => '1-1-2010',
            'value' => -1000,
            'submit' => true
            );
        $this->setExpectedException('C3op_Form_ProjectEditException');
        $form->process($formData);
    }
    
    /*
     *  
     */
    public function testUserCanUseAccentedCharactersWhenEditing() {
        
       $project = new C3op_Projects_Project();
       $project->SetTitle("some title");
       $this->projectMapper->insert($project);
       $id = $project->GetId();
        
       $form = new C3op_Form_ProjectEdit();
       $formData = array(
           'id' => $id,
           'title' => 'I scream, you scream, we still scream to icecream and úÀÇÃêã é or ç?!?!?...;- yeah!',
           'dateBegin' => '',
           'value' => '',
           'submit' => true
           );
       $form->process($formData);
        
    }
    
     
}