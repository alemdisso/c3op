<?php
require_once 'PHPUnit/Extensions/Database/TestCase.php';

class ProjectDBTest extends PHPUnit_Extensions_Database_TestCase {
    protected $pdo;
    
    public function __construct() {
        $this->pdo = PHPUnit_Util_PDO::factory(
           'mysql:host=localhost;dbname=testing_c3op'
        );
        Project::createTable($this->pdo);
        
    }
    public function getConnection() {
        return $this->createDefaultDBConnection($pdo, 'c3op_test');
    }   

    public function getDataSet() {
        return $this->createXMLDataSet(TEST_DIR . '/Fixtures/test.xml');
    }

    public function testRowCount() {
        $this->assertGreaterThan(0, $this->getConnection()->getRowCount('test'));
    }
}