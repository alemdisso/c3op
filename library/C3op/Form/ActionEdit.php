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

        $this->removeElement('submit');

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
            $action->SetResponsible($data['responsible']);
            $action->SetMilestone($data['milestone']);
            $action->SetRequirementForReceiving($data['requirementForReceiving']);

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

            $actionMapper->update($action);
            return $id;
        }
    }
 }