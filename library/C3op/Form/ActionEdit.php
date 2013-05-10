<?php
class C3op_Form_ActionEdit extends C3op_Form_ActionCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editActionForm')
            ->setAction('/projects/action/edit')
            ->setMethod('post');

        $element = new Zend_Form_Element_Hidden('id');
        $element->addValidator('Int')
            ->addFilter('StringTrim');
        $this->addElement($element);
        $element->setDecorators(array('ViewHelper'));

        $this->removeElement('submit');
        $this->removeElement('subordinatedTo');

        $user = Zend_Registry::get('user');
        $role = $user->GetRole();
        if ($role == C3op_Access_RolesConstants::ROLE_SYSADMIN) {
            $element = new Zend_Form_Element_Select('status');
            $element->setLabel('#Status:')
                    ->setDecorators(array(
                        'ViewHelper',
                        'Errors',
                        array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'three columns inset-by-eight omega')),
                        array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                    ))
                    ->setOptions(array('class' => 'Full alpha omega'));
            $obj = new C3op_Projects_ActionStatusTypes();
            $statusTypes = $obj->AllStatus();
            while (list($key, $title) = each($statusTypes)) {
                $element->addMultiOption($key, _($title));
            }
            $this->addElement($element);
        }

        $element = new Zend_Form_Element_Select('subordinatedTo');
        $element->setLabel('#Subordinated to: ') //Subordinada a:
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'nine columns omega')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
                ->setRegisterInArrayValidator(false);
        $element->addMultiOption(0, _("#(no action)"));
        $this->addElement($element);

        $element = new Zend_Form_Element_Checkbox('product');
        $element->setLabel('#This action is a product?')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'one column')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'three columns alpha Right')),
                ))
                ->setOptions(array('checked' => '1', 'unChecked' => '0'))
                ->setValue('0')
                ;
        $this->addElement($element);

        $element = new Zend_Form_Element_Select('requirementForReceiving');
        $element->setLabel('#Is requirement for receiving: ') //'É requisito para receber: '
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tagClass' => 'div', 'class' => 'four columns')),
                    array('Label', array('tag' => 'div', 'tagClass' => 'four columns alpha Right')),
                ))
                ->setOptions(array('class' => 'Full alpha omega'))
                ->setRegisterInArrayValidator(false);
        $element->addMultiOption(0, _("#(not a requirement for receiving)")); // (não é requisito para recebimento)
        $this->addElement($element);

        // create submit button
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('#Submit')
               ->setDecorators(array('ViewHelper','Errors',
                    array(array('data' => 'HtmlTag'),
                    array('tag' => 'div','class' => 'two columns inset-by-nine omega')),
                    array('Label',
                      array('tag' => 'div','tagClass' => 'three columns alpha Invisible')
                    ),
                  ))
               ->setOptions(array('class' => 'submit Full alpha omega'));
        $this->addElement($submit);

    }

    public function process($data) {

        $db = Zend_Registry::get('db');
        $actionMapper = new C3op_Projects_ActionMapper($db);

        if ($this->isValid($data) !== true) {
            throw new C3op_Form_ActionEditException('Invalid data!');
        } else {
            $id = $data['id'];
            $action = $actionMapper->findById($id);
            $action->SetTitle($data['title']);
            $action->SetProject($data['project']);
            if (isset($data['status'])) {
                $action->SetStatus($data['status']);
            }
            $action->SetDescription($data['description']);
            $action->SetSubordinatedTo($data['subordinatedTo']);
            $action->setSupervisor($data['supervisor']);
            $action->SetRequirementForReceiving($data['requirementForReceiving']);
//            $action->SetMilestone($data['milestone']);
//            $action->SetRequirementForReceiving($data['requirementForReceiving']);

            $action->SetProduct($data['product']);

            $baselineBeginDate = $data['baselineBeginDate'];
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($baselineBeginDate)) {
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($baselineBeginDate);
                $action->SetBaselineBeginDate($dateForMysql);
            }

            $baselineFinishDate = $data['baselineFinishDate'];
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($baselineFinishDate)){
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($baselineFinishDate);
                $action->SetBaselineFinishDate($dateForMysql);
            }

            $predictedBeginDate = $data['predictedBeginDate'];
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedBeginDate)) {
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedBeginDate);
                $action->SetPredictedBeginDate($dateForMysql);
            }

            $predictedFinishDate = $data['predictedFinishDate'];
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($predictedFinishDate)){
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($predictedFinishDate);
                $action->SetPredictedFinishDate($dateForMysql);
            }

            $realBeginDate = $data['realBeginDate'];
            $dateValidator = new C3op_Util_ValidDate();
            if ($dateValidator->isValid($realBeginDate)) {
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($realBeginDate);
                $action->SetRealBeginDate($dateForMysql);
            }

            $realFinishDate = $data['realFinishDate'];
            $dateValidator = new C3op_Util_ValidDate();
            $dateForMysql = null;
            if ($dateValidator->isValid($realFinishDate)) {
                $converter = new C3op_Util_DateConverter();
                $dateForMysql = $converter->convertDateToMySQLFormat($realFinishDate);
            }
            $action->SetRealFinishDate($dateForMysql);

            $actionMapper->update($action);
            return $id;
        }
    }
 }