<?php
  class C3op_Form_ProjectCreate extends Zend_Form {
    public function init() {
      $this->setName('newProjectForm')
            ->setAction('/projects/project/create')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');
      $element = new Zend_Form_Element_Text('title');
      $titleValidator = new C3op_Projects_ProjectValidTitle();
      $element->setLabel('#Name:')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
              ->setOptions(array('class' => 'eleven columns alpha omega'))
              ->addErrorMessage(_("#Title is required"))
              ->setRequired(true)
              ->addFilter('StringTrim')
              ->addValidator($titleValidator, true);
      $this->addElement($element);

      $element = new Zend_Form_Element_Text('shortTitle');
      $titleValidator = new C3op_Projects_ProjectValidTitle();
      $element->setLabel('#Short title:')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
              ->setOptions(array('class' => 'eleven columns alpha omega'))
              ->addErrorMessage(_("#Short title is required"))
              ->setRequired(true)
              ->addFilter('StringTrim')
              ->addValidator($titleValidator, true);
      $this->addElement($element);

      $element = new Zend_Form_Element_Select('client', array('onChange' => 'javascript:populateResponsibleAtClient()'));
      $element->setLabel('#Client: ')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'nine columns')),
                array('Description', array('escape' => false, 'tag' => 'div', 'class' => 'two columns omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
              ->setOptions(array('class' => 'nine columns alpha omega'))
              ->setDescription('<a href="/register/institution/create" class="two columns button alpha omega">' . _("#New client") . '</a>')
              ->setRegisterInArrayValidator(false);
      $element->addMultiOption(0, _("#choose a client"));
      $this->addElement($element);

      $element = new Zend_Form_Element_Select('ourResponsible');
      $element->setLabel('#Our responsible')
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
      $element->addMultiOption(0, _("#(choose a person)"));
      $this->addElement($element);

      $element = new Zend_Form_Element_Select('responsibleAtClient');
      $element->setLabel('#Responsible at client')
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
      $element->addMultiOption(0, _("#(choose a person)"));
      $this->addElement($element);

      $element = new Zend_Form_Element_Text('beginDate');
      $dateValidator = new C3op_Util_ValidDate();
      $element->setLabel('#Begin date:')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
              ->setOptions(array('class' => 'two columns alpha omega datepicker'))
              ->setRequired(false)
              ->addValidator($dateValidator)
              ->addFilter('StringTrim');
      $this->addElement($element);

      $element = new Zend_Form_Element_Text('finishDate');
      $element->setLabel('#Finish date:')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns inset-by-five omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
              ->setOptions(array('class' => 'two columns alpha omega datepicker'))
              ->setRequired(false)
              ->addValidator('date')
              ->addFilter('HtmlEntities')
              ->addFilter('StringTrim');
      $this->addElement($element);

      $element = new Zend_Form_Element_Text('value');
      $element->setLabel('#Value:')
              ->setAttrib('alt','decimal')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
              ->setOptions(array('class' => 'two columns alpha omega'))
              ->setRequired(false)
              ->addValidator(new C3op_Util_ValidFloat)
              ->addFilter('StringTrim');
      $this->addElement($element);

    $user = Zend_Registry::get('user');
    $role = $user->GetRole();
    if ($role == C3op_Access_RolesConstants::ROLE_SYSADMIN) {
        $element = new Zend_Form_Element_Select('status');
        $element->setLabel('#Project status:')
                ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-four omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
                ))
                ->setOptions(array('class' => 'three columns alpha omega'));

        $obj = new C3op_Projects_ProjectStatusTypes();
        $titleTypes = $obj->AllTitles();
        $element->addMultiOption(null, _("#(choose a status)"));
        while (list($key, $type) = each($titleTypes)) {
        $element->addMultiOption($key, $type);
        }
        $this->addElement($element);
    } else {
        $element = new Zend_Form_Element_Hidden('status');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $element = new Zend_Form_Element_Select('nilStatus');
        $element->setLabel('#Project status:')
                ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-four omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
                ))
                ->setOptions(array('class' => 'three columns alpha omega'));

        $element->addMultiOption(null, _("#(disabled)"));
        $element->setAttrib('disabled', 'disabled');
        $this->addElement($element);


    }

      $element = new Zend_Form_Element_Text('overhead');
      $element->setLabel('#Overhead:')
                 ->setAttrib('alt','decimal')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
              ->setOptions(array('class' => 'two columns alpha omega'))
              ->setRequired(false)
              ->addValidator(new C3op_Util_ValidFloat)
        ;
      $this->addElement($element);

      $element = new Zend_Form_Element_Text('managementFee');
      $element->setLabel('#Management fee:')
                 ->setAttrib('alt','decimal')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'two columns inset-by-three omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
              ->setOptions(array('class' => 'two columns alpha omega'))
              ->setRequired(false)
              ->addValidator(new C3op_Util_ValidFloat)
              ->addFilter('HtmlEntities')
              ->addFilter('StringTrim');
      $this->addElement($element);

      $element = new Zend_Form_Element_Select('contractNature');
      $element->setLabel('#Contract nature:')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'four columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
              ->setOptions(array('class' => 'four columns alpha omega'));

      $obj = new C3op_Projects_ContractNatureTypes();
      $titleTypes = $obj->AllTitles();
      $element->addMultiOption(null, _("#(choose a type)"));
      while (list($key, $type) = each($titleTypes)) {
        $element->addMultiOption($key, $type);
      }
      $this->addElement($element);

      $element = new Zend_Form_Element_Select('areaActivity');
      $element->setLabel('#Area activity:')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'five columns omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'two columns Right')),
              ))
              ->setOptions(array('class' => 'five columns alpha omega'));
      $obj = new C3op_Projects_AreaActivityTypes();
      $titleTypes = $obj->AllTitles();
      $element->addMultiOption(null, _("#(choose an area)"));
      while (list($key, $type) = each($titleTypes)) {
        $element->addMultiOption($key, $type);
      }
      $this->addElement($element);

      $element = new Zend_Form_Element_Textarea('object');
      $element->setLabel('#Object:')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
              ->setAttrib('rows','8')
              ->setOptions(array('class' => 'eleven columns alpha omega'))
              ->setRequired(false)
              ->addFilter('StringTrim');
      $this->addElement($element);

      $element = new Zend_Form_Element_Textarea('summary');
      $element->setLabel('#Summary:')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
              ->setAttrib('rows','8')
              ->setOptions(array('class' => 'eleven columns alpha omega'))
              ->setRequired(false)
              ->addFilter('StringTrim');
      $this->addElement($element);

      $element = new Zend_Form_Element_Textarea('observation');
      $element->setLabel('#Observation:')
              ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'eleven columns omega')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
              ))
              ->setAttrib('rows','8')
              ->setOptions(array('class' => 'eleven columns alpha omega'))
              ->setRequired(false)
              ->addFilter('StringTrim');
      $this->addElement($element);

      // create submit button
      $submit = new Zend_Form_Element_Submit('submit');
      $submit->setLabel('#Submit')
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

      if ($this->isValid($data) !== true) {
        throw new C3op_Form_ProjectCreateException('Invalid data!');
      } else {
        $db = Zend_Registry::get('db');
        $projectMapper = new C3op_Projects_ProjectMapper($db);

        $project = new C3op_Projects_Project();
        $project->SetTitle($this->title->GetValue());
        $project->SetShortTitle($this->shortTitle->GetValue());
        $project->SetClient($this->client->GetValue());
        $project->SetOurResponsible($this->ourResponsible->GetValue());
        $project->SetResponsibleAtClient($this->responsibleAtClient->GetValue());

        $beginDate = $this->beginDate->GetValue();
        $dateValidator = new C3op_Util_ValidDate();
        if ($dateValidator->isValid($beginDate)) {
          $converter = new C3op_Util_DateConverter();
          $beginDateForMysql = $converter->convertDateToMySQLFormat($beginDate);
          $project->SetBeginDate($beginDateForMysql);
        }

        $finishDate = $this->finishDate->GetValue();
        $dateValidator = new C3op_Util_ValidDate();
        if ($dateValidator->isValid($finishDate)) {
          $converter = new C3op_Util_DateConverter();
          $finishDateForMysql = $converter->convertDateToMySQLFormat($finishDate);
          $project->SetFinishDate($finishDateForMysql);
        }

        $value = $this->status->GetValue();
        if ($value) {
            $project->SetStatus($this->status->GetValue());
        } else {
            $project->SetStatus(C3op_Projects_ProjectStatusConstants::STATUS_NIL);
        }
        $project->SetContractNature($this->contractNature->GetValue());
        $project->SetAreaActivity($this->areaActivity->GetValue());

        $converter = new C3op_Util_FloatConverter();
        $validator = new C3op_Util_ValidFloat();
        if ($validator->isValid($this->value->GetValue())) {
            $projectValue = $converter->getDecimalDotValue($this->value->GetValue(), $validator);
            $project->SetValue($projectValue);
        }
        $value = $this->overhead->GetValue();
        if ($value > 0) {
            $project->SetOverhead($converter->getDecimalDotValue($value, $validator));
        }
        $value = $this->managementFee->GetValue();
        if ($value > 0) {
            $project->SetManagementFee($converter->getDecimalDotValue($value, $validator));
        }
        $project->SetObject($this->object->GetValue());
        $project->SetSummary($this->summary->GetValue());
        $project->SetObservation($this->observation->GetValue());


        $projectMapper->insert($project);

        $contractMapper = new C3op_Projects_ContractMapper($db);

        $contract = new C3op_Projects_Contract($project->getId(), $beginDateForMysql, false);
        $contract->SetBeginDate($beginDateForMysql);
        $contract->SetFinishDate($finishDateForMysql);
        $contract->SetContractNature($this->contractNature->GetValue());
        $contract->SetValue($projectValue);
        $contractMapper->insert($contract);



      }
    }
  }