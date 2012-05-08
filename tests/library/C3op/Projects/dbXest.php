<?php
require_once "PHPUnit/Extensions/Database/TestCase.php";

class DbTest extends ControllerTestCase {
    
    public function testCanTest() {
        $this->assertEquals(300, 300);
    }
    
    protected function getDatabaseTester()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=c3op', 'root', '');
        $connection = new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($pdo, 'c3op');
        $tester = new PHPUnit_Extensions_Database_DefaultTester($connection);
        $tester->setSetUpOperation(PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT());
        $tester->setTearDownOperation(PHPUnit_Extensions_Database_Operation_Factory::NONE());
        $tester->setDataSet(new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(dirname(__FILE__).'/fixture/projects_seed.xml'));
        
        return $tester;
    }    


    public function testNewAccountCreation()
    {
        $tester = $this->getDatabaseTester();
        $tester->onSetUp();

        $project = new C3op_Projects_Project();
        $project->setTitle("more other project");
        
        $xml_dataset = new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(dirname(__FILE__).'/fixture/projects_after_inclusion.xml');
        PHPUnit_Extensions_Database_TestCase::assertDataSetsEqual($xml_dataset, $tester->getConnection()->createDataSet());
        
        $tester->onTearDown();
    }


}
