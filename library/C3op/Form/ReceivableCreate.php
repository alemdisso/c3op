<?php
class C3op_Form_ReceivableCreate extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('newReceivableForm')
            ->setAction('/projects/receivable/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');
        
        $project = new Zend_Form_Element_Hidden('project');
        $project->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');        
        $this->addElement($project);
        $project->setDecorators(array('ViewHelper'));

        //$this->addElementText('title', 'Recebimento', new C3op_Util_ValidString, 50);
        
        $elementText = new Zend_Form_Element_Text('title');
        $elementText->setLabel('Recebimento')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);
        
        // $this->addElementText('predictedDate', 'Data Prevista', new C3op_Util_ValidDate, 50);

        $elementText = new Zend_Form_Element_Text('predictedDate');
        $elementText->setLabel('Data Prevista')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'three columns alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);

        //$this->addElementText('realDate', 'Data Realizada', new C3op_Util_ValidDate, 50);
        
        $elementText = new Zend_Form_Element_Text('realDate');
        $elementText->setLabel('Data Realizada')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-three omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
            ))
            ->setOptions(array('class' => 'three columns alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);

        // $this->addElementText('predictedValue', 'Valor Previsto', new C3op_Util_ValidPositiveFloat, 50);

        $elementText = new Zend_Form_Element_Text('predictedValue');
        $elementText->setLabel('Valor Previsto')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'three columns alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);

        // $this->addElementText('realValue', 'Valor Realizado', new C3op_Util_ValidPositiveFloat, 50);
        
        $elementText = new Zend_Form_Element_Text('realValue');
        $elementText->setLabel('Valor Realizado')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-three omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
            ))
            ->setOptions(array('class' => 'three columns alpha omega'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Gravar')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'five columns inset-by-six omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Invisible')),
              ))
            ->setOptions(array('class' => 'submit two columns alpha omega'));
        $this->addElement($submit);

    }
    
    public function process($data) {
        
        
        if ($this->isValid($data) !== true) 
        {
            throw new C3op_Form_ReceivableCreateException('Invalid data!');
        } 
        else
        {
            $db = Zend_Registry::get('db');
            $receivableMapper = new C3op_Projects_ReceivableMapper($db);
            
            $predictedDate = $this->predictedDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedDate))
            {
                $converter = new C3op_Util_DateConverter();                
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedDate);
                $predictedDateConvertedToMySQL = $dateForMysql;
            }
            
            $realDate = $this->realDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($realDate))
            {
                $converter = new C3op_Util_DateConverter();                
                $dateForMysql = $converter->convertDateToMySQLFormat($realDate);
                $realDateConvertedToMySQL = $dateForMysql;
                $receivable->SetRealDate($realDateConvertedToMySQL);
            }
            
            
            
            $receivable = new C3op_Projects_Receivable($this->project->GetValue(),$predictedDateConvertedToMySQL, $this->predictedValue->GetValue());
            $receivable->SetTitle($this->title->GetValue());
            $receivable->SetProject((float)$this->project->GetValue());
            $receivable->SetRealValue((float)$this->realValue->GetValue());
            
            $receivableMapper->insert($receivable);
        }
    }
 
    private function addElementText($fieldName, $label, $validator, $fieldSize)
    {
        $elementText = new Zend_Form_Element_Text($fieldName);
        $elementText->setLabel($label)
            ->setOptions(array('size' => "$fieldSize"))
            ->addValidator($validator)
            ->addFilter('StringTrim')
                ;
        $this->addElement($elementText);
        
    }
     
 }