<?php

/*
 * In the role of Manager
 * I want to be able to create a project
 * so I can manage this project
 * 
 * > just a project with title
 * Given: user at CreateProject form
 * When: user inputs title project and submits
 * Then: new project is created
 * 
 * > lacking title
 * Given: user at CreateProject form
 * When: user submits the form without title
 * Then: no project is created
 * 
 * 
 * 
 */

/**
 * Description of GestoraCriaProjetoSuiteTest
 *
 * @author Rodrigo Machado
 */
class GestoraCriaProjetoSuiteTest {
    
    public function setUp() {
        parent::setUp();
    }
    
    public function testCreateProjectJustWithTitle()
    {
        $p = new C3op_Projects_Project();
    }
        
    public function testErrorWhenTryToCreateProjectWithEmptyTitle()
    {
        
    }
        
       
    
 
}
