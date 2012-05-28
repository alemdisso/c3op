<?php

class ProjectControllerTest extends ControllerTestCase
{
    private $createController;
    //private $editController;
    private $inserts=array();
    private $projectMapper;
    private $db;
    
    public function setUp() {
        
        parent::setup();
        $this->db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($this->db);        
        
    }
    
    public function tearDown() {
        reset ($this->inserts);
        foreach($this->inserts as $id => $insert) {
            if (!$insert['persistence']) {
                $this->projectMapper->delete($insert['p']);
                unset($this->inserts[$id]);
            }
        }
        parent::tearDown();
    }
    
    private function insertProjectForTesting($title, $persistence=false) {
        $p = new C3op_Projects_Project();
        $p->SetTitle($title);
        $this->projectMapper->insert($p);
        $this->inserts[$p->getId()] = array(
                'p'             => $p,
                'persistence'   => $persistence
            );
        
        return $p;
    }

   private function initController() {
        return new Projects_ProjectController(
            $this->request,
            $this->response,
            $this->request->getParams()
        );
    }
    
    public function testIfCanFindPageToCreateAProject() {
        $this->dispatch('/projects/project/create');
        $this->createController = $this->initController();
        $this->createController->createAction();
        $this->assertController('project');
        $this->assertAction('create');
    }

    public function testFindFormToCreateProject()
    {
        $this->dispatch('/projects/project/create');
        $this->createController = $this->initController();
        $this->createController->createAction();
        $viewVars = $this->createController->view->getVars();
        $this->assertArrayHasKey('form', $viewVars);        
    }
    

   /*
    * @depends testFindFormToCreateProject
    */
   public function testIfPostCreateFormReallySavesAProject() {
       $titleInserted = 'salvei um projeto';
       $formData = array(
           'title' => $titleInserted,
           'submit' => true
           );
        $this->dispatch('/projects/project/create');
        $projectController = new Projects_ProjectController(
            $this->request->setMethod('POST')->setPost($formData),
            $this->response,
            $this->request->getParams()
        );
        $projectController->createAction();
        $this->assertController('project');
        $this->assertAction('create');
        
        $list = $this->projectMapper->getAllIds();
        $find = false;
        foreach ($list as $id) {
            $thisProject = $this->projectMapper->findById($id);
            if ($thisProject->GetTitle() == $titleInserted) {
                $find = true;
                $p = $thisProject;
            }
        }
        $this->assertTrue($find);
        $this->inserts[$p->getId()] = array(
                'p'             => $p,
                'persistence'   => false
            );
        
    }
    
    public function testMessageConfirmingProjectCreation() {
       $titleInserted = 'salvei um projeto';
       $formData = array(
           'title' => $titleInserted,
           'submit' => true
           );
        $this->dispatch('/projects/project/create');
        $projectController = new Projects_ProjectController(
            $this->request->setMethod('POST')->setPost($formData),
            $this->response,
            $this->request->getParams()
        );
        $projectController->createAction();
        $this->assertController('project');
        $this->assertAction('create');
        
        $list = $this->projectMapper->getAllIds();
        $find = false;
        foreach ($list as $id) {
            $thisProject = $this->projectMapper->findById($id);
            if ($thisProject->GetTitle() == $titleInserted) {
                $find = true;
                $p = $thisProject;
            }
        }
        $this->assertTrue($find);
        $this->inserts[$p->getId()] = array(
                'p'             => $p,
                'persistence'   => false
            );
        
        $this->assertRedirectTo('/projects/project/success-create');
        
    }
    /*
     * 
     */
    public function testIfCanFindPageToEditAProject() {
        $titleToInsert = 'a project we want to edit';
        $p = $this->insertProjectForTesting($titleToInsert, false);        
        $this->dispatch('/projects/project/edit/?id=' . $p->getId());
        $this->editController = $this->initController();
        $this->editController->editAction();
        $viewVars = $this->editController->view->getVars();
        $this->assertArrayHasKey('form', $viewVars);
        $this->assertController('project');
        $this->assertAction('edit');
        $form = $viewVars['form'];
        $this->assertEquals('Zend_Form_Element_Text', get_class($form->getElement('title')));
        $titleField = $form->getElement('title');
        $titleFromForm = $titleField->getValue();
        $this->assertEquals($titleToInsert, $titleFromForm);
        $idField = $form->getElement('id');
        $idFromForm = $idField->getValue();
        $this->assertEquals($p->GetId(), $idFromForm);
    }
    
    public function testIfJustNumbersAreAcceptedAsIdFromGet() {        
        $this->setExpectedException('C3op_Projects_ProjectException');
        $this->dispatch("/projects/project/edit/?id=anything111' OR ");
        $this->editController = $this->initController();
        $this->editController->editAction();
    }

    public function testIfCanChangeTitleOfProjectWhenEditing() {
        $titleToInsert = 'a project we will change title';
        $p = $this->insertProjectForTesting($titleToInsert, false);                        
        $projectId = $p->GetId();
        $titleChanged = 'this is better';
        $formData = array(
            'title' => $titleChanged,
            'value' => '',
           'dateBegin' => '',
            'id' => $projectId,
            'submit' => true
            );
        $this->dispatch('/projects/project/edit');
        $updateController = new Projects_ProjectController(
            $this->request->setMethod('POST')->setPost($formData),
            $this->response,
            $this->request->getParams()
        );
        $updateController->editAction();
        
        
        $otherMapper = new C3op_Projects_ProjectMapper($this->db);
        $projectAfterUpdate = $otherMapper->findById($projectId);      
        $this->assertEquals($titleChanged, $projectAfterUpdate->GetTitle());
    }
 
    public function testThatRightDateIsSavedInMySqlFormat() {
        $titleInserted = 'a project we want to check converted date';
        $y = "2012";
        $m = "05";
        $d = "01";
        $dateInserted = "$d-$m-$y";
        $timestampInserted = mktime(0,0,0,$m,$d,$y);
        $formData = array(
           'title' => $titleInserted,
           'dateBegin' => $dateInserted,
           'submit' => true
           );
        $this->dispatch('/projects/project/create');
        $projectController = new Projects_ProjectController(
            $this->request->setMethod('POST')->setPost($formData),
            $this->response,
            $this->request->getParams()
        );
        $projectController->createAction();
      
        $list = $this->projectMapper->getAllIds();
        $find = false;
        $p = new C3op_Projects_Project();
        foreach ($list as $id) {
            $thisProject = $this->projectMapper->findById($id);
            if ($thisProject->GetTitle() == $titleInserted) {
                $find = true;
                $p = $thisProject;
            }
        }
        if ($find) {            
            $savedDate = $p->GetDateBegin();
            $dateArray = explode("-", $savedDate);
            $timestampSaved = mktime(0,0,0,$dateArray[1],$dateArray[2],$dateArray[0]);
            $this->assertEquals($timestampInserted, $timestampSaved);
        } else {
            $this->assertTrue(false);
        }
        
        $this->projectMapper->delete($p);
        
        
    }
    
    public function testIfSavedDateIsShownAtFormInBrazilianStyle() {
        $titleToInsert = 'a project we want to check a date';
        $dateInserted = "2012-09-07";
        
        $p = new C3op_Projects_Project();
        $p->SetTitle($titleToInsert);
        $p->SetDateBegin($dateInserted);
        $this->projectMapper->insert($p);
        $this->inserts[$p->getId()] = array(
                'p'             => $p,
                'persistence'   => false
            );
        
        $this->dispatch('/projects/project/edit/?id=' . $p->getId());
        $this->editController = $this->initController();
        $this->editController->editAction();
        $viewVars = $this->editController->view->getVars();
        $form = $viewVars['form'];
        $this->assertEquals('Zend_Form_Element_Text', get_class($form->getElement('dateBegin')));
        $dateField = $form->getElement('dateBegin');
        $dateFromForm = $dateField->getValue();
        $dateArray = explode("-", $dateInserted);
        $formatedDate = $dateArray[2] . '/' . $dateArray[1] . '/' . $dateArray[0]; 
        $this->assertEquals($formatedDate, $dateFromForm);
    }
    
    
   
    
    
}