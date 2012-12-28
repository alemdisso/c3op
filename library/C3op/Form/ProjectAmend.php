<?php
class C3op_Form_ProjectAmend extends Zend_Form
{
    public function init()
    {

        // initialize form
        $this->setName('newContractForm')
            ->setAction('/projects/project/amend')
            ->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'Area')),'Form'))
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('id');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

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

        $element = new Zend_Form_Element_Text('signingDate');
        $element->setLabel('#Signing Date')
            ->setAttrib('alt','date')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns')),
                array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
            ))
            ->setOptions(array('class' => 'Full alpha omega datepicker'))
            ->addValidator(new C3op_Util_ValidString)
            ->addFilter('StringTrim')
                ;
        $this->addElement($element);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit ->setLabel('#Submit')
                ->setDecorators(array('ViewHelper','Errors',
                    array(array('data' => 'HtmlTag'),
                    array('tag' => 'div','class' => 'two columns inset-by-nine omega')),
                    array('Label',
                      array('tag' => 'div','tagClass' => 'three columns alpha Invisible')
                    ),
                  ))
                ->setOptions(array('class' => 'submit Full alpha omega'));
        $this   ->addElement($submit);
    }

    public function process($data) {

        if ($this->isValid($data) !== true)
        {
            throw new C3op_Form_ContractCreateException('Invalid data!');
        }
        else
        {
            $db = Zend_Registry::get('db');
            $contractMapper = new C3op_Projects_ContractMapper($db);

            $signingDate = $this->signingDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($signingDate))
            {
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($signingDate);
                $signingDateConvertedToMySQL = $dateForMysql;
            } else {
                throw new C3op_Form_ContractCreateException('Invalid signing date!');
            }

            $contract = new C3op_Projects_Contract($this->id->GetValue(),$signingDateConvertedToMySQL, true);
            $contract->SetObservation($this->observation->GetValue());

            $contractMapper->insert($contract);
            return $contract->getProject();

        }
    }

 }