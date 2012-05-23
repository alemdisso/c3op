<?php
class C3op_Form_ProjectCreate extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('newProjectForm')
            ->setAction('/projects/project/create')
            ->setMethod('post');
        
        // create text input for title
        $title = new Zend_Form_Element_Text('title');
//        $titleValidator = new Zend_Validate_Regex("/^[0-9a-zA-ZÀ-ú]+[0-9A-Za-zÀ-ú\'\[\]\(\)\-\.\,\:\;\!\? ]{1,50}$/");
        $titleValidator = new C3op_Projects_ProjectValidTitle();
        $title->setLabel('Nome:')
            ->setOptions(array('size' => '50'))
            ->setRequired(true)
            ->addValidator($titleValidator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim')
                ;
        // attach elements to form
        $this->addElement($title);
        
        // create text input for date begin
        class_exists('C3op_Util_ValidDate') || require APPLICATION_PATH . "/../library/C3op/Util/validDate.php";

        $dateBegin = new Zend_Form_Element_Text('dateBegin');
        $dateValidator = new C3op_Util_ValidDate();
        $dateBegin->setLabel('Data de início:')
            ->setOptions(array('size' => '35'))
            ->setRequired(false)
            ->addValidator($dateValidator)
//            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        // attach elements to form
        $this->addElement($dateBegin);
        
        
        // create text input for date finish
        $dateFinish = new Zend_Form_Element_Text('date_finish');
        $dateFinish->setLabel('Data de término:')
            ->setOptions(array('size' => '35'))
            ->setRequired(false)
            ->addValidator('date')
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        // attach elements to form
        $this->addElement($dateFinish);
        
        // create text input for value
        $value = new Zend_Form_Element_Text('value');
        $value->setLabel('Valor:')
            ->setOptions(array('size' => '35'))
            ->setRequired(false)
            ->addValidator('Regex', false, array(
                'pattern' => '/^[0-9]*\.?[0-9]*$/'
                ))
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        // attach elements to form
        $this->addElement($value);
        
        // create text input for overhead
        $overhead = new Zend_Form_Element_Text('overhead');
        $overhead->setLabel('Overhead:')
            ->setOptions(array('size' => '35'))
            ->setRequired(false)
            ->addValidator('Regex', false, array(
                'pattern' => '/^[0-9]*\.?[0-9]*$/'
                ))
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        // attach elements to form
        $this->addElement($overhead);
        
        // create text input for management fee
        $managementFee = new Zend_Form_Element_Text('management_fee');
        $managementFee->setLabel('Taxa de Administração:')
            ->setOptions(array('size' => '35'))
            ->setRequired(false)
            ->addValidator('Regex', false, array(
                'pattern' => '/^[0-9]*\.?[0-9]*$/'
                ))
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        // attach elements to form
        $this->addElement($managementFee);
        
        
        // create text input for object
        $object = new Zend_Form_Element_TextArea('object');
        $object->setLabel('Objeto:')
            ->setAttrib('cols','8')
            ->setAttrib('rows','5')
            ->setRequired(false)
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        // attach elements to form
        $this->addElement($object);
        
        // create text input for summery
        $summary = new Zend_Form_Element_TextArea('summary');
        $summary->setLabel('Sumário:')
            ->setAttrib('cols','8')
            ->setAttrib('rows','5')
            ->setRequired(false)
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        // attach elements to form
        $this->addElement($summary);
        
        // create text input for obs
        $observation = new Zend_Form_Element_TextArea('observation');
        $observation->setLabel('Observações:')
            ->setAttrib('cols','8')
            ->setAttrib('rows','5')
            ->setRequired(false)
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        // attach elements to form
        $this->addElement($observation);
        
        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Salvar')
            ->setOptions(array('class' => 'submit'));
        $this->addElement($submit);
                

    }
    
    public function process($data) {
        
        
        if ($this->isValid($data) !== true) 
        {
            throw new C3op_Form_ProjectCreateException('Invalid data!');
        } 
        else
        {
            $db = Zend_Registry::get('db');
            $projectMapper = new C3op_Projects_ProjectMapper($db);
            
            $project = new C3op_Projects_Project();
            $project->SetTitle($this->title->GetValue());
            $project->SetValue($this->value->GetValue());
            
            $dateBegin = $this->dateBegin->GetValue();
            //class_exists('C3op_Util_ValidDate') || require APPLICATION_PATH . "/../library/C3op/Util/validDate.php";
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($dateBegin))
            {
                $converter = new C3op_Util_DateConverter();                
                $dateForMysql = $converter->convertDateToMySQLFormat($dateBegin);
                $project->SetDateBegin($dateForMysql);
            }
            
            $projectMapper->insert($project);
        }
    }
 }