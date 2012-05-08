<?php

class ProjectMapperTest extends ControllerTestCase
{
    private $projectMapper;
    private $inserts=array();
    
    public function setUp() {
        parent::setUp();
        $db = Zend_Registry::get('db');
        $this->projectMapper = new C3op_Projects_ProjectMapper($db);        
    }
    
    public function tearDown() {
        reset ($this->inserts);
        foreach($this->inserts as $id => $insert) {
            if (!$insert['persistence']) {
                $this->projectMapper->delete($insert['p']);
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
    
    public function testIfCanInsertAProject() {
        $p = new C3op_Projects_Project();
        $this->AssertEquals(0, $p->GetId());
        $this->AssertEquals("", $p->GetTitle());
        $titleInserted =  '1st project inserted';
        $p = $this->insertProjectForTesting($titleInserted, false);        
        $this->AssertFalse(0 == $p->GetId());
        $this->AssertEquals($titleInserted, $p->GetTitle());
    }
    
    public function testIfCanListProjectsIds() {
        $list = $this->projectMapper->getAllIds();
        $initialCount = count($list);
        $titleInserted = 'other project inserted';
        $p = $this->insertProjectForTesting($titleInserted, false);
        $this->assertTrue($p->getId() > 0);
        
        $list = $this->projectMapper->getAllIds();
        $currentCount = count($list);
        $this->assertTrue($currentCount - $initialCount == 1);
        
        $find = false;
        foreach ($list as $id) {
            $thisProject = $this->projectMapper->findById($id);
            if ($thisProject->GetTitle() == $titleInserted) {
                $find = true;
            }
        }
        $this->assertTrue($find);
    }
    
    public function testThatIsNotPossibleToChangeAProjectId()
    {   
        $titleInserted = 'a project inserted to try to change id';
        $p = $this->insertProjectForTesting($titleInserted, false);
        $projectId = $p->getId();
        $newId = $projectId * 7;
        $this->setExpectedException('C3op_Projects_ProjectException');
        $p->SetId($newId);   
        $this->assertEquals($p->getId(), $projectId);
    }
 
    public function testThatIsNotPossibleToDeleteAnUnsavedProject()
    {   
        $p = new C3op_Projects_Project();
        $p->SetTitle('qq coisa');
        $this->setExpectedException('C3op_Projects_ProjectMapperException');
        $this->projectMapper->delete($p);
    }
 
    public function testThatIsNotPossibleToFindAProjectThatDoesntExist()
    {   
        $firstTitleInserted = 'a project inserted...';
        $p1 = $this->insertProjectForTesting($firstTitleInserted, false);
        $secondTitleInserted = 'and other project inserted';
        $p1 = $this->insertProjectForTesting($secondTitleInserted, false);
        $list = $this->projectMapper->getAllIds();
        $maxId = 0;
        foreach ($list as $id) {
            if ($id > $maxId) {
                $maxId = $id;
            }
        }
        $this->setExpectedException('C3op_Projects_ProjectMapperException');
        $nonExistentId = $maxId + 1;
        $impossibleProject = $this->projectMapper->findById($nonExistentId);
        
        
    }
    
    public function testIfBeginDateIsSaved()
    {
        $title = 'when? why? ';
        $dateBegin = "13-06-2012";
        $p = new C3op_Projects_Project();
        $p->SetTitle($title);
        class_exists('C3op_Util_ValidDate') || require APPLICATION_PATH . "/../library/C3op/Util/validDate.php";
        $dateConverter = new C3op_Util_DateConverter();
        $p->SetDateBegin($dateConverter->convertDateToMySQLFormat($dateBegin));
        $this->projectMapper->insert($p);
        $this->inserts[$p->getId()] = array(
                'p'             => $p,
                'persistence'   => false
            );
        $id = $p->getId();
        
        $p2 = $this->projectMapper->findById($id);
        $this->assertEquals($p2->GetDateBegin(), $dateConverter->convertDateToMySQLFormat($dateBegin));

    }
 
    public function testThatIsPossibleToChangeABeginDate()
    {   
        $titleInserted = 'HOHOHO a project inserted to try to change date';
        $dateBeginAtFirst = "25-12-2011";
        $p = new C3op_Projects_Project();
        $p->SetTitle($titleInserted);
        class_exists('C3op_Util_ValidDate') || require APPLICATION_PATH . "/../library/C3op/Util/validDate.php";
        $dateConverter = new C3op_Util_DateConverter();
        $p->SetDateBegin($dateConverter->convertDateToMySQLFormat($dateBeginAtFirst));
        $this->projectMapper->insert($p);
        $this->inserts[$p->getId()] = array(
                'p'             => $p,
                'persistence'   => false
            );
        $id = $p->getId();
        $p2 = $this->projectMapper->findById($id);
        $newDate = "2012-05-01";
        $p2->SetDateBegin($newDate);   
        $this->assertEquals($p->getDateBegin(), $newDate);
    }
 
    public function testThatIsNotPossibleToUpdateAProjectBeforeInsertingIt()
    {   
        $p = new C3op_Projects_Project();
        $p->SetTitle('no id project');
        $this->setExpectedException('C3op_Projects_ProjectMapperException');
        $this->projectMapper->update($p);
    }
 
 
    
}
