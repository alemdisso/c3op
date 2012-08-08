<?php
class C3op_Form_OutlayDojoCreate extends Zend_Dojo_Form
{
    public $humanResourcesList;
    public function __construct($options = null)
    {
        parent::__construct($options);
        //$this->setAction($options['action'])->setMethod('post');
        // initialize form
        

        $this->setName('newOutlayForm')
        ->setAction('/projects/outlay/create')
        ->setMethod('post');

        
        $humanResource = new Zend_Form_Element_Hidden('humanResource');
        $humanResource->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($humanResource);
        
        $project = new Zend_Form_Element_Hidden('project');
        $project->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($project);
        
        $action = new Zend_Form_Element_Hidden('action');
        $action->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($action);
        
        $this->setDecorators(array(
                'FormElements',
                array(
                    'TabContainer',
                     array(
                        'id' => 'tabContainer',
                        'style' => 'width:660px; height:500px',
                        'dijitParams' => array(
                            'tabPosition' => 'top',
                        )
                    ),
                    'DijitForm'
                )
            )); 
        
        // create rich editor
        $observation = new Zend_Dojo_Form_Element_Editor('observation');
        $observation->setLabel('Observation:')
            ->setOptions(array(
                'width' => '120px',
                'height' => '40px',
                ));        
        $this->addElement($observation);
        
        
        
        
    }   
 }