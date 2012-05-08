<?php
require_once 'PHPUnit/Extensions/Database/TestCase.php';

class TryTest extends PHPUnit_Extensions_Database_TestCase {
    public function getConnection() {
        $pdo = new PDO('mysql:host=localhost;dbname=c3op', 'root', '');
        return $this->createDefaultDBConnection($pdo, 'c3op_test');
    }   

    public function getDataSet() {
        return $this->createXMLDataSet(TEST_DIR . '/Fixtures/test.xml');
    }

    public function testRowCount() {
        $this->assertGreaterThan(0, $this->getConnection()->getRowCount('test'));
    }
}