<?php

class C3op_Projects_ProjectMapper
{

    protected $db;
    protected $identityMap;

    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $query = $this->db->prepare('SELECT id FROM projects_projects WHERE 1=1;');
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function insert(C3op_Projects_Project $obj) {


       $query = $this->db->prepare("INSERT INTO projects_projects (title, short_title, client, our_responsible,
            responsible_at_client, begin_date, finish_date, status,
            value, contract_nature, area_activity, overhead,
            management_fee, object, summary, observation)
            VALUES (:title, :short_title, :client, :our_responsible
            , :responsible_at_client, :begin_date, :finish_date, :status
            , :value, :contract_nature, :area_activity, :overhead
            , :management_fee, :object, :summary, :observation)");


        $query->bindValue(':title', $obj->getTitle(), PDO::PARAM_STR);
        $query->bindValue(':short_title', $obj->getShortTitle(), PDO::PARAM_STR);
        $query->bindValue(':client', $obj->getClient(), PDO::PARAM_STR);
        $query->bindValue(':our_responsible', $obj->getOurResponsible(), PDO::PARAM_STR);
        $query->bindValue(':responsible_at_client', $obj->getResponsibleAtClient(), PDO::PARAM_STR);
        $query->bindValue(':begin_date', $obj->getBeginDate(), PDO::PARAM_STR);
        $query->bindValue(':finish_date', $obj->getFinishDate(), PDO::PARAM_STR);
        $query->bindValue(':status', $obj->getStatus(), PDO::PARAM_STR);
        $query->bindValue(':value', $obj->getValue(), PDO::PARAM_STR);
        $query->bindValue(':contract_nature', $obj->getContractNature(), PDO::PARAM_STR);
        $query->bindValue(':area_activity', $obj->getAreaActivity(), PDO::PARAM_STR);
        $query->bindValue(':overhead', $obj->getOverhead(), PDO::PARAM_STR);
        $query->bindValue(':management_fee', $obj->getManagementFee(), PDO::PARAM_STR);
        $query->bindValue(':object', $obj->getObject(), PDO::PARAM_STR);
        $query->bindValue(':summary', $obj->getSummary(), PDO::PARAM_STR);
        $query->bindValue(':observation', $obj->getObservation(), PDO::PARAM_STR);

        $query->execute();

        $obj->setId((int)$this->db->lastInsertId());
        $this->identityMap[$obj] = $obj->getId();

    }

    public function update(C3op_Projects_Project $obj) {

        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_ContractMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE projects_projects
            SET title = :title, short_title = :short_title, client = :client, our_responsible = :our_responsible
            ,  responsible_at_client = :responsible_at_client, begin_date = :begin_date, finish_date = :finish_date, status = :status
            ,  value = :value, contract_nature = :contract_nature, area_activity = :area_activity, overhead = :overhead
            ,  management_fee = :management_fee, object = :object, summary = :summary, observation = :observation
            WHERE id = :id;");

        $query->bindValue(':title', $obj->getTitle(), PDO::PARAM_STR);
        $query->bindValue(':short_title', $obj->getShortTitle(), PDO::PARAM_STR);
        $query->bindValue(':client', $obj->getClient(), PDO::PARAM_STR);
        $query->bindValue(':our_responsible', $obj->getOurResponsible(), PDO::PARAM_STR);
        $query->bindValue(':responsible_at_client', $obj->getResponsibleAtClient(), PDO::PARAM_STR);
        $query->bindValue(':begin_date', $obj->getBeginDate(), PDO::PARAM_STR);
        $query->bindValue(':finish_date', $obj->getFinishDate(), PDO::PARAM_STR);
        $query->bindValue(':status', $obj->getStatus(), PDO::PARAM_STR);
        $query->bindValue(':value', $obj->getValue(), PDO::PARAM_STR);
        $query->bindValue(':contract_nature', $obj->getContractNature(), PDO::PARAM_STR);
        $query->bindValue(':area_activity', $obj->getAreaActivity(), PDO::PARAM_STR);
        $query->bindValue(':overhead', $obj->getOverhead(), PDO::PARAM_STR);
        $query->bindValue(':management_fee', $obj->getManagementFee(), PDO::PARAM_STR);
        $query->bindValue(':object', $obj->getObject(), PDO::PARAM_STR);
        $query->bindValue(':summary', $obj->getSummary(), PDO::PARAM_STR);
        $query->bindValue(':observation', $obj->getObservation(), PDO::PARAM_STR);
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Projects_ActionException("sql failed");
        }

    }

    public function findById($id) {

        $this->identityMap->rewind();
        while ($this->identityMap->valid()) {
            if ($this->identityMap->getInfo() == $id) {
                return $this->identityMap->current();
            }
            $this->identityMap->next();
        }

        $query = $this->db->prepare('SELECT title
                    , short_title
                    , client
                    , our_responsible
                    , responsible_at_client
                    , begin_date
                    , finish_date
                    , status
                    , value
                    , contract_nature
                    , area_activity
                    , overhead
                    , management_fee
                    , object
                    , summary
                    , observation FROM projects_projects WHERE id = :id;');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();

        $result = $query->fetch();

        if (empty($result)) {
            throw new C3op_Projects_ContractMapperException(sprintf('There is no contract with id #%d.', $id));
        }
        $obj = new C3op_Projects_Project();

        $this->setAttributeValue($obj, $id, 'id');
        $this->setAttributeValue($obj, $result['title'], 'title');
        $this->setAttributeValue($obj, $result['short_title'], 'shortTitle');
        $this->setAttributeValue($obj, $result['client'], 'client');
        $this->setAttributeValue($obj, $result['our_responsible'], 'ourResponsible');
        $this->setAttributeValue($obj, $result['responsible_at_client'], 'responsibleAtClient');
        $this->setAttributeValue($obj, $result['begin_date'], 'beginDate');
        $this->setAttributeValue($obj, $result['finish_date'], 'finishDate');
        $this->setAttributeValue($obj, $result['status'], 'status');
        $this->setAttributeValue($obj, $result['value'], 'value');
        $this->setAttributeValue($obj, $result['contract_nature'], 'contractNature');
        $this->setAttributeValue($obj, $result['area_activity'], 'areaActivity');
        $this->setAttributeValue($obj, $result['overhead'], 'overhead');
        $this->setAttributeValue($obj, $result['management_fee'], 'managementFee');
        $this->setAttributeValue($obj, $result['object'], 'object');
        $this->setAttributeValue($obj, $result['summary'], 'summary');
        $this->setAttributeValue($obj, $result['observation'], 'observation');


        $this->identityMap[$obj] = $id;

        return $obj;
    }

    public function delete(C3op_Projects_Project $p) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_ContractMapperException('Object has no ID, cannot delete.');
        }
        $query = $this->db->prepare('DELETE FROM projects_projects WHERE id = :id;');
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();
        unset($this->identityMap[$obj]);
    }

    public function getAllActions(C3op_Projects_Project $obj)
    {
        $query = $this->db->prepare('SELECT id FROM projects_actions WHERE project = :project');
        $query->bindValue(':project', $obj->getId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllProducts(C3op_Projects_Project $obj)
    {
        $query = $this->db->prepare('SELECT id FROM projects_actions WHERE project = :project AND subordinated_to = 0 AND requirement_for_receiving > 0;');
        $query->bindValue(':project', $obj->getId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }

    public function getAllReceivables(C3op_Projects_Project $obj)
    {
        $query = $this->db->prepare('SELECT id FROM projects_receivables WHERE project = :project;');
        $query->bindValue(':project', $obj->getId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }

    public function getAllActionsSubordinatedTo(C3op_Projects_Project $obj, $actionId=0)
    {
        if ($actionId >= 0) {

            $query = $this->db->prepare('SELECT id FROM projects_actions WHERE project = :project AND (subordinated_to IS NULL OR subordinated_to = :subordinated_to)');
            $query->bindValue(':project', $obj->getId(), PDO::PARAM_STR);
            $query->bindValue(':subordinated_to', $actionId, PDO::PARAM_STR);
            $query->execute();
            $resultPDO = $query->fetchAll();

            $result = array();
            foreach ($resultPDO as $row) {
                $result[] = $row['id'];
            }

            return $result;
        } else throw new C3op_Projects_ActionMapperException("invalid action id to find subordinated for");
    }

    public function getAllMaterialSuppliesContractedOrPredictedAt(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT s.id
            FROM projects_actions a
            INNER JOIN projects_material_supplies s ON a.id = s.action
            WHERE a.project = %d
            AND (
            s.status = %d
            OR s.status = %d
            OR s.status = %d
            OR s.status = %d
            )'
            , $obj->getId()
            , C3op_Projects_MaterialSupplyStatusConstants::STATUS_UNDEFINED
            , C3op_Projects_MaterialSupplyStatusConstants::STATUS_CONTRACTED
            , C3op_Projects_MaterialSupplyStatusConstants::STATUS_ACQUITTED
            , C3op_Projects_MaterialSupplyStatusConstants::STATUS_FORESEEN
                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllProductsOf(C3op_Projects_Project $p)
    {
            return $this->getAllActionsSubordinatedTo($p, 0);
    }

    private function setAttributeValue(C3op_Projects_Project $p, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($p, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($p, $fieldValue);
    }

    public function getAllOutlaysOf(C3op_Projects_Project $p) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT o.id, o.predicted_date
                    FROM projects_outlays o
                    INNER JOIN projects_actions a ON a.id = o.action
                    INNER JOIN projects_team_members t ON t.id = o.team_member
                    WHERE o.project = %d AND t.linkage > 0', $p->getId()
                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllOutlaysRelatedToDoneActions(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT o.id, o.predicted_date
                    FROM projects_outlays o
                    INNER JOIN projects_actions a ON a.id = o.action
                    INNER JOIN projects_team_members t ON t.id = o.team_member
                    WHERE a.done = 1 AND o.project = %d AND t.linkage > 0 ORDER BY o.predicted_date', $obj->getId()
                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllOutsideServicesContractedOrPredictedAt(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT s.id
            FROM projects_actions a
            INNER JOIN projects_outside_services s ON a.id = s.action
            WHERE a.project = %d
            AND (
            s.status = %d
            OR s.status = %d
            OR s.status = %d
            OR s.status = %d
            )'
            , $obj->getId()
            , C3op_Projects_OutsideServiceStatusConstants::STATUS_UNDEFINED
            , C3op_Projects_OutsideServiceStatusConstants::STATUS_CONTRACTED
            , C3op_Projects_OutsideServiceStatusConstants::STATUS_ACQUITTED
            , C3op_Projects_OutsideServiceStatusConstants::STATUS_FORESEEN
                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllDoneActions(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT a.id
                    FROM projects_actions a
                    WHERE a.done = 1 AND a.project = %d ', $obj->getId()
                )) as $row) {
            $result[] = $row['id'];
        }

        return $result;
    }

    public function getAllUnacknowledgededActions(C3op_Projects_Project $obj
                                        , C3op_Projects_ActionMapper $actionMapper) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT a.id
                    FROM projects_actions a
                    INNER JOIN projects_actions_dates d
                    ON a.id = d.action
                    WHERE a.status = %d AND a.project = %d ORDER BY d.real_begin_date'
                , C3op_Projects_ActionStatusConstants::STATUS_IN_EXECUTION
                , $obj->getId()
                )) as $row) {

            $action = $actionMapper->findById($row['id']);
            $obj = new C3op_Projects_ActionStartMode($action, $actionMapper);
            if ($obj->isUnacknowledged()) {
                $result[] = $row['id'];
            }
        }
        return $result;
    }

    public function getAllTeamMembersContractedAt(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT t.id
            FROM projects_actions a
            INNER JOIN projects_team_members t ON a.id = t.action
            WHERE t.linkage > 0
            AND a.project = %d
            AND (
            a.status = %d
            OR a.status = %d
            )'
            , $obj->getId()
            , C3op_Projects_TeamMemberStatusConstants::STATUS_CONTRACTED
            , C3op_Projects_TeamMemberStatusConstants::STATUS_ACQUITTED

                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllTeamMembersContractedOrPredictedAt(C3op_Projects_Project $obj) {
        $result = array();

        foreach ($this->db->query(sprintf('SELECT t.id
            FROM projects_actions a
            INNER JOIN projects_team_members t ON a.id = t.action
            WHERE a.project = %d
            AND (
            t.status = %d
            OR t.status = %d
            OR t.status = %d
            OR t.status = %d
            )'
            , $obj->getId()
            , C3op_Projects_TeamMemberStatusConstants::STATUS_UNDEFINED
            , C3op_Projects_TeamMemberStatusConstants::STATUS_CONTRACTED
            , C3op_Projects_TeamMemberStatusConstants::STATUS_ACQUITTED
            , C3op_Projects_TeamMemberStatusConstants::STATUS_FORESEEN

                )) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllAmendments(C3op_Projects_Project $obj)
    {
        $query = $this->db->prepare('SELECT id FROM projects_contracts WHERE project = :project AND amendment = true;');
        $query->bindValue(':project', $obj->getId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }

    public function getAllContracts(C3op_Projects_Project $obj)
    {
        $query = $this->db->prepare('SELECT id FROM projects_contracts WHERE project = :project;');
        $query->bindValue(':project', $obj->getId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }

    public function createContract(C3op_Projects_Project $obj)
    {
        $contractMapper = new C3op_Projects_ContractMapper($this->db);

        $contract = new C3op_Projects_Contract($obj->getId(), $obj->getBeginDate(), false);
        $contract->setBeginDate($obj->getBeginDate());
        $contract->setFinishDate($obj->getFinishDate());
        $contract->setContractNature($obj->getContractNature());
        if (!is_null($obj->getValue())) {
            $contract->setValue($obj->getValue());
        } else {
            $contract->setValue(0);
        }
        $contract->setValue($obj->getValue());
        if (!is_null($obj->getManagementFee())) {
            $contract->setManagementFee($obj->getManagementFee());
        } else {
            $contract->setManagementFee(0);
        }
        $contract->setObject($obj->getObject());
        $contract->setSummary($obj->getSummary());
        $contract->setObservation($obj->getObservation());
        $contractMapper->insert($contract);


    }



}