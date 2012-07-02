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
            
            $project->SetValue($this->value->GetValue());
            $project->SetStatus($this->status->GetValue());
            $project->SetContractNature($this->contractNature->GetValue());
            $project->SetAreaActivity($this->areaActivity->GetValue());
            $project->SetOverhead($this->overhead->GetValue());
            $project->SetManagementFee($this->managementFee->GetValue());
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