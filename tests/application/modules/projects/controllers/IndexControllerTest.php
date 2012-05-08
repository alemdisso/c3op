<?php

class IndexControllerTest extends ControllerTestCase
{
    private $createController;
    private $inserts=array();
    private $projectMapper;
    
    public function setUp() {
        
        parent::setup();
        $db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($db);        
        
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
    
    private function createNewControllerAndFiresAction() {
        $this->createController = new Projects_IndexController(
            $this->request,
            $this->response,
            $this->request->getParams()
        );
        $this->createController->indexAction();
        
    }

    public function testThereIsListOfProjectsAtProjectsIndex()
    {
        $this->insertProjectForTesting('1st project inserted to see at list', false);
        $this->insertProjectForTesting('other project inserted to list', false);        
        $mapperList = $this->projectMapper->getAllIds();
        $this->dispatch('/projects');
        $this->createNewControllerAndFiresAction();
        $this->assertModule('projects');
        $this->assertController('index');
        $this->assertAction('index');
        
        $viewVars = $this->createController->view->getVars();
        $this->assertArrayHasKey('projectsList', $viewVars);
        $projectsList = $viewVars['projectsList'];
        $this->assertEquals(count($mapperList), count($projectsList));
    }
    
    public function testDatesAndValuesShownAtIndex()
    {

        $titleToInsert = 'a project we want to see with date';
        $dateInserted = "2012-09-07";

        $p = new C3op_Projects_Project();
        $p->SetTitle($titleToInsert);
        $p->SetDateBegin($dateInserted);
        $this->projectMapper->insert($p);
        $this->inserts[$p->getId()] = array(
                'p'             => $p,
                'persistence'   => false
            );
        
        $titleToInsert = 'a project we want to see with date and value';
        $dateInserted = "2012-10-01";
        $value = 1234;

        $p = new C3op_Projects_Project();
        $p->SetTitle($titleToInsert);
        $p->SetDateBegin($dateInserted);
        $p->SetValue($value);
        $this->projectMapper->insert($p);
        $this->inserts[$p->getId()] = array(
                'p'             => $p,
                'persistence'   => false
            );
        
        $titleToInsert = 'a project we want to see just with value';
        $value = 8743000;

        $p = new C3op_Projects_Project();
        $p->SetTitle($titleToInsert);
        $p->SetDateBegin($dateInserted);
        $p->SetValue($value);
        $this->projectMapper->insert($p);
        $this->inserts[$p->getId()] = array(
                'p'             => $p,
                'persistence'   => false
            );
        
        //$mapperList = $this->projectMapper->getAllIds();
        $this->dispatch('/projects');
        $this->createNewControllerAndFiresAction();
        $this->assertModule('projects');
        $this->assertController('index');
        $this->assertAction('index');
        
        $viewVars = $this->createController->view->getVars();
        $this->assertArrayHasKey('projectsList', $viewVars);
        $projectsList = $viewVars['projectsList'];
         
        
        while (list($id, $projectData) = each($projectsList)) {
            $p = $this->projectMapper->findById($id);
            
            $this->assertEquals($p->GetDateBegin(), $projectData['dateBegin']);
            if ($p->GetValue() > 0) {
                $this->assertEquals($p->GetValue(), (float)$projectData['value']);
            }
        }
        
        
        
    }
    
/*
 * @depends testThereIsListOfProjectsAtProjectsIndex
 */    
    public function testThereAreLinksToEditProjectsAtList()
    {
        $this->insertProjectForTesting('more one project inserted to see at list', false);
        $this->insertProjectForTesting('and another project inserted to list', false);        
        $this->dispatch('/projects');
        $this->createNewControllerAndFiresAction();
        $this->assertModule('projects');
        $this->assertController('index');
        $this->assertAction('index');
        
        $viewVars = $this->createController->view->getVars();
        $this->assertArrayHasKey('projectsList', $viewVars);
        $projectsList = $viewVars['projectsList'];
        
        while (list($id, $projectData) = each($projectsList)) {
            if (preg_match("#/projects/project/edit/\?id=([0-9]+)#", $projectData['linkEdit'], $matches)) {
                $this->assertEquals($id, $matches[1]);
            } else {
                $this->assertEquals(false, $projectData['linkEdit']);
            }
        }
        
    }
    
    public function testThereIsALinkToCreateNewProject()
    {
        $this->dispatch('/projects');
        $this->createNewControllerAndFiresAction();
        $this->assertModule('projects');
        $this->assertController('index');
        $this->assertAction('index');
        
        $viewVars = $this->createController->view->getVars();
        $this->assertArrayHasKey('createProjectLink', $viewVars);
        $createProjectLink = $viewVars['createProjectLink'];
        $this->assertEquals($createProjectLink, '/projects/project/create');
    }
    
/*
 * @depends testThereIsListOfProjectsAtProjectsIndex
 * @depends testThereAreLinksToEditProjectsAtList
 */    
    
    public function testCleanAllProjectsInsertedToTest() {
        reset ($this->inserts);
        foreach($this->inserts as $id => $insert) {
            $this->projectMapper->delete($insert['p']);
            unset ($this->inserts[$id]);
        }
        $this->assertEquals(0, count($this->inserts));
    }
 
}