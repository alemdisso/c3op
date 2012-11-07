<?php
class C3op_Form_ProjectEdit extends C3op_Form_ProjectCreate
{
    public function init()
    {
        parent::init();

        // initialize form
        $this->setName('editProjectForm')
            ->setAction('/projects/project/edit')
            ->setMethod('post');

        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidator('Int')
            //->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        $this->addElement($id);
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

             $id = $data['id'];
            $project = $projectMapper->findById($id);
            $project->SetTitle($this->title->GetValue());
            $project->SetClient($this->client->GetValue());
            $project->SetOurResponsible($this->ourResponsible->GetValue());
            $project->SetResponsibleAtClient($this->responsibleAtClient->GetValue());

            $beginDate = $this->beginDate->GetValue();
            $project->SetBeginDate($this->prepareDateValueToSet($beginDate, new C3op_Util_ValidDate(), new C3op_Util_DateConverter()));
            $finishDate = $this->finishDate->GetValue();
            $project->SetFinishDate($this->prepareDateValueToSet($finishDate, new C3op_Util_ValidDate(), new C3op_Util_DateConverter()));

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
                $project->SetValue($converter->getDecimalDotValue($this->value->GetValue(), $validator));
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

            $projectMapper->update($project);
        }
    }

    private function prepareDateValueToSet($value, C3op_Util_ValidDate $validator, C3op_Util_DateConverter $converter)
    {
        if ($validator->isValid($value)) {
            return $converter->convertDateToMySQLFormat($value);
        } else {
            return "0000-00-00";
        }
    }

}