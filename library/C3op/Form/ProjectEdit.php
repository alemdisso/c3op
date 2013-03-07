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
            $project->SetShortTitle($this->shortTitle->GetValue());
            $project->SetClient($this->client->GetValue());
            $project->SetOurResponsible($this->ourResponsible->GetValue());
            $project->SetResponsibleAtClient($this->responsibleAtClient->GetValue());

            $beginDate = $this->beginDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            $beginDateForMysql = null;
            if ($dateValidator->isValid($beginDate)) {
            $converter = new C3op_Util_DateConverter();
            $beginDateForMysql = $converter->convertDateToMySQLFormat($beginDate);
            $project->SetBeginDate($beginDateForMysql);
            }

            $finishDate = $this->finishDate->GetValue();
            $dateValidator = new C3op_Util_ValidDate();
            $finishDateForMysql = null;
            if ($dateValidator->isValid($finishDate)) {
                $converter = new C3op_Util_DateConverter();
                $finishDateForMysql = $converter->convertDateToMySQLFormat($finishDate);
                $project->SetFinishDate($finishDateForMysql);
            }

//            $beginDate = $this->beginDate->GetValue();
//            $project->SetBeginDate($this->prepareDateValueToSet($beginDate, new C3op_Util_ValidDate(), new C3op_Util_DateConverter()));
//            $finishDate = $this->finishDate->GetValue();
//            $project->SetFinishDate($this->prepareDateValueToSet($finishDate, new C3op_Util_ValidDate(), new C3op_Util_DateConverter()));

            $value = $this->status->GetValue();
            if ($value) {
                $project->SetStatus($this->status->GetValue());
            } else {
                $project->SetStatus(C3op_Projects_ProjectStatusConstants::STATUS_NIL);
            }
            $project->SetContractNature($this->contractNature->GetValue());
            $project->SetAreaActivity($this->areaActivity->GetValue());

            $converter = new C3op_Util_DecimalConverter();
            $validator = new C3op_Util_ValidDecimal();
            $projectValue = 0;
            if ($validator->isValid($this->value->GetValue())) {
                $projectValue = $converter->getDecimalDotValue($this->value->GetValue(), $validator);
                $project->SetValue($projectValue);
            }
//            if ($validator->isValid($this->value->GetValue())) {
//                $project->SetValue($converter->getDecimalDotValue($this->value->GetValue(), $validator));
//            }
            $value = $this->overhead->GetValue();
            if ($value > 0) {
                $project->SetOverhead($converter->getDecimalDotValue($value, $validator));
            }
            $managementFee = $this->managementFee->GetValue();
            if ($value > 0) {
                $project->SetManagementFee($converter->getDecimalDotValue($value, $validator));
            }

            $project->SetObject($this->object->GetValue());
            $project->SetSummary($this->summary->GetValue());
            $project->SetObservation($this->observation->GetValue());

            $projectMapper->update($project);

            $doesIt = new C3op_Projects_ProjectSeemsToBeContracted($project);
            if ($doesIt->seemsToBeContracted()) {

                $contracts = $projectMapper->getAllContracts($project);
                $contractMapper = new C3op_Projects_ContractMapper($db);
                if (count($contracts)) {
                    $contract = $contractMapper->findById($contracts[0]);
        //                $projectsList[$id]['projectName'] = $thisProject->GetShortTitle() . "!!!";
                } else {
                    $contract = new C3op_Projects_Contract($project->getId(), $beginDateForMysql, false);
                }

                $contract->SetBeginDate($beginDateForMysql);
                $contract->SetFinishDate($finishDateForMysql);
                $contract->SetContractNature($this->contractNature->GetValue());
                $contract->SetValue($projectValue);
                $contract->SetManagementFee($managementFee);
                $contract->SetObject($this->object->GetValue());
                $contract->SetSummary($this->summary->GetValue());
                $contract->SetObservation($this->observation->GetValue());
                if (count($contracts)) {
                    $contractMapper->update($contract);
                } else {
                    $contractMapper->insert($contract);
                }

            }


        }
    }

    private function prepareDateValueToSet($value, C3op_Util_ValidDate $validator, C3op_Util_DateConverter $converter)
    {
        if ($validator->isValid($value)) {
            return $converter->convertDateToMySQLFormat($value);
        } else {
            return null;
        }
    }

}