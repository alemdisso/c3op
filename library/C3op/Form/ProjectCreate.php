<?php
class C3op_Form_ProjectCreate extends Zend_Form
{
    public function init()
    {
        $this->setName('newProjectForm')
            ->setAction('/projects/project/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $title = new Zend_Form_Element_Text('title');
        $titleValidator = new C3op_Projects_ProjectValidTitle();
        $title->setLabel('Nome:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->setRequired(true)
            ->addValidator($titleValidator)
            ->addFilter('StringTrim')
                ;
        $this->addElement($title);
        
        $client = new Zend_Form_Element_Select('client');
        $client->setLabel('Cliente: ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'nine columns')),
                    array('Description', array('escape' => false, 'tag' => 'div', 'class' => 'two columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'nine columns alpha omega'))
                ->setDescription('<a href="/register/institution/create" class="two columns button alpha omega">Novo cliente</a>')
                ->setRegisterInArrayValidator(false);
        $client->addMultiOption(0, "escolha um cliente");
        $this->addElement($client);
        
  
        $ourResponsible = new Zend_Form_Element_Select('ourResponsible');
        $ourResponsible->setLabel('Responsável pelo IETS: ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'nine columns')),
                    array('Description', array('escape' => false, 'tag' => 'div', 'class' => 'two columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'nine columns alpha omega'))
                ->setDescription('<a href="/register/contact/create" class="two columns button alpha omega">Novo responsável</a>')
                ->setRegisterInArrayValidator(false);
        $ourResponsible->addMultiOption(0, "escolha uma pessoa");
        $this->addElement($ourResponsible);
        
        $responsibleAtClient = new Zend_Form_Element_Select('responsibleAtClient');
        $responsibleAtClient->setLabel('Responsável pelo cliente: ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'nine columns')),
                    array('Description', array('escape' => false, 'tag' => 'div', 'class' => 'two columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('class' => 'nine columns alpha omega'))
                ->setDescription('<a href="/register/contact/create" class="two columns button alpha omega">Novo responsável</a>')
                ->setRegisterInArrayValidator(false);
        $responsibleAtClient->addMultiOption(0, "escolha uma pessoa");
        $this->addElement($responsibleAtClient);
        
        $beginDate = new Zend_Form_Element_Text('beginDate');
        $dateValidator = new C3op_Util_ValidDate();
        $beginDate->setLabel('Data de início:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'two columns alpha omega'))
            ->setRequired(false)
            ->addValidator($dateValidator)
            ->addFilter('StringTrim');
        $this->addElement($beginDate);
        
        $finishDate = new Zend_Form_Element_Text('finishDate');
        $finishDate->setLabel('Data de término:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns inset-by-five omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
            ->setOptions(array('class' => 'two columns alpha omega'))
            ->setRequired(false)
            ->addValidator('date')
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($finishDate);
        
        $value = new Zend_Form_Element_Text('value');
        $value->setLabel('Valor:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'two columns alpha omega'))
            ->setRequired(false)
            ->addValidator('Regex', false, array(
                'pattern' => '/^[0-9]*\.?[0-9]*$/'
                ))
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        // attach elements to form
        $this->addElement($value);
        
        $status = new Zend_Form_Element_Select('status');
        $status->setLabel('Status do Projeto:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-four omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
            ->setOptions(array('class' => 'three columns alpha omega'))
        ;
        $titleTypes = C3op_Projects_ProjectStatusTypes::AllTitles();
        $status->addMultiOption(null, "(escolha um status)");
        while (list($key, $title) = each($titleTypes)) {
            $status->addMultiOption($key, $title);
        }        
        $this->addElement($status);

        $contractNature = new Zend_Form_Element_Select('contractNature');
        $contractNature->setLabel('Natureza do Contrato:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'four columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'four columns alpha omega'))
        ;
        $titleTypes = C3op_Projects_ContractNatureTypes::AllTitles();
        $contractNature->addMultiOption(null, "(escolha um tipo)");
        while (list($key, $title) = each($titleTypes)) {
            $contractNature->addMultiOption($key, $title);
        }        
        $this->addElement($contractNature);
        
        $areaActivity = new Zend_Form_Element_Select('areaActivity');
        $areaActivity->setLabel('Área de atuação:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'five columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
            ->setOptions(array('class' => 'five columns alpha omega'))
        ;
        $titleTypes = C3op_Projects_AreaActivityTypes::AllTitles();
        $areaActivity->addMultiOption(null, "(escolha uma área)");
        while (list($key, $title) = each($titleTypes)) {
            $areaActivity->addMultiOption($key, $title);
        }        
        $this->addElement($areaActivity);

        $overhead = new Zend_Form_Element_Text('overhead');
        $overhead->setLabel('Overhead:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'two columns alpha omega'))
            ->setRequired(false)
            ->addValidator('Regex', false, array(
                'pattern' => '/^[0-9]*\.?[0-9]*$/'
                ));
        $this->addElement($overhead);
        
        $managementFee = new Zend_Form_Element_Text('managementFee');
        $managementFee->setLabel('Taxa de Administração:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns inset-by-three omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setOptions(array('class' => 'two columns alpha omega'))
            ->setRequired(false)
            ->addValidator('Regex', false, array(
                'pattern' => '/^[0-9]*\.?[0-9]*$/'
                ))
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($managementFee);

        $object = new Zend_Form_Element_Textarea('object');
        $object->setLabel('Objeto:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setAttrib('rows','8')
            ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->setRequired(false)
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($object);

        $summary = new Zend_Form_Element_Textarea('summary');
        $summary->setLabel('Sumário:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setAttrib('rows','8')
            ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->setRequired(false)
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($summary);
        
        $observation = new Zend_Form_Element_Textarea('observation');
        $observation->setLabel('Observações:')
              ->setDecorators(array(
                  'ViewHelper',
                  'Errors',
                  array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                  array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
            ->setAttrib('rows','8')
            ->setOptions(array('class' => 'eleven columns alpha omega'))
            ->setRequired(false)
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($observation);
        
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
    
    public function process($data)
    {
        
        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ProjectCreateException('Invalid data!');
        } 
        else
        {
            $db = Zend_Registry::get('db');
            $projectMapper = new C3op_Projects_ProjectMapper($db);
            
            $project = new C3op_Projects_Project();
            $project->SetTitle($this->title->GetValue());
            $project->SetClient($this->client->GetValue());
            $project->SetOurResponsible($this->ourResponsible->GetValue());
            $project->SetResponsibleAtClient($this->responsibleAtClient->GetValue());
            
            $beginDate = $this->beginDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($beginDate)) {
                $converter = new C3op_Util_DateConverter();                
                $dateForMysql = $converter->convertDateToMySQLFormat($beginDate);
                $project->SetBeginDate($dateForMysql);
            }
            
            $finishDate = $this->finishDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($finishDate)){
                $converter = new C3op_Util_DateConverter();                
                $dateForMysql = $converter->convertDateToMySQLFormat($finishDate);
                $project->SetFinishDate($dateForMysql);
            }
            $project->SetValue($this->value->GetValue());
            $project->SetStatus($this->status->GetValue());
            $project->SetContractNature($this->contractNature->GetValue());
            $project->SetAreaActivity($this->areaActivity->GetValue());
            $project->SetOverhead($this->overhead->GetValue());
            $project->SetManagementFee($this->managementFee->GetValue());
            $project->SetObject($this->object->GetValue());
            $project->SetSummary($this->summary->GetValue());
            $project->SetObservation($this->observation->GetValue());
            
            $projectMapper->insert($project);
        }
    }
 }