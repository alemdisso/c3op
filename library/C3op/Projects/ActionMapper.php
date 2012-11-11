<?php

class C3op_Projects_ActionMapper
{

    protected $db;
    protected $identityMap;

    function __construct()
    {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds()
    {
        $query = $this->db->prepare('SELECT id FROM projects_actions WHERE 1=1;');
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }

    public function insert(C3op_Projects_Action $obj)
    {

        $query = $this->db->prepare("INSERT INTO projects_actions (title, project, done, status, description, subordinated_to, responsible, milestone, requirement_for_receiving) VALUES (:title, :project, :done, :status, :description, :subordinated_to, :responsible, :milestone, :requirement_for_receiving)");

        $query->bindValue(':title', $obj->GetTitle(), PDO::PARAM_STR);
        $query->bindValue(':project', $obj->GetProject(), PDO::PARAM_STR);
        $query->bindValue(':done', $obj->GetDone(), PDO::PARAM_STR);
        $query->bindValue(':status', $obj->GetStatus(), PDO::PARAM_STR);
        $query->bindValue(':description', $obj->GetDescription(), PDO::PARAM_STR);
        $query->bindValue(':subordinated_to', $obj->GetSubordinatedTo(), PDO::PARAM_STR);
        $query->bindValue(':responsible', $obj->GetResponsible(), PDO::PARAM_STR);
        $query->bindValue(':milestone', $obj->GetMilestone(), PDO::PARAM_STR);
        $query->bindValue(':requirement_for_receiving', $obj->GetRequirementForReceiving(), PDO::PARAM_STR);

        $query->execute();

        $obj->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$obj] = $obj->GetId();

        $this->insertDates($obj);

    }

    public function update(C3op_Projects_Action $obj)
    {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_ActionMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE projects_actions SET title = :title, project = :project, done = :done, status = :status, description = :description, subordinated_to = :subordinated_to, responsible = :responsible, milestone = :milestone, requirement_for_receiving = :requirement_for_receiving WHERE id = :id;");

        $query->bindValue(':title', $obj->GetTitle(), PDO::PARAM_STR);
        $query->bindValue(':project', $obj->GetProject(), PDO::PARAM_STR);
        $query->bindValue(':done', $obj->GetDone(), PDO::PARAM_STR);
        $query->bindValue(':status', $obj->GetStatus(), PDO::PARAM_STR);
        $query->bindValue(':description', $obj->GetDescription(), PDO::PARAM_STR);
        $query->bindValue(':subordinated_to', $obj->GetSubordinatedTo(), PDO::PARAM_STR);
        $query->bindValue(':responsible', $obj->GetResponsible(), PDO::PARAM_STR);
        $query->bindValue(':milestone', $obj->GetMilestone(), PDO::PARAM_STR);
        $query->bindValue(':requirement_for_receiving', $obj->GetRequirementForReceiving(), PDO::PARAM_STR);
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Projects_ActionException("$sql failed");
        }

        $this->updateDates($obj);
    }

    public function findById($id)
    {
        $this->identityMap->rewind();
        while ($this->identityMap->valid()) {
            if ($this->identityMap->getInfo() == $id) {
                return $this->identityMap->current();
            }
            $this->identityMap->next();
        }
        $query = $this->db->prepare('SELECT title, project, done, status, description, subordinated_to, responsible, milestone, requirement_for_receiving FROM projects_actions WHERE id = :id;');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if (empty($result)) {
            throw new C3op_Projects_ActionMapperException(sprintf('There is no action with id #%d.', $id));
        }

        $project = $result['project'];

        $obj = new C3op_Projects_Action($project);
        $this->setAttributeValue($obj, $id, 'id');
        $this->setAttributeValue($obj, $result['title'], 'title');
        $this->setAttributeValue($obj, $result['project'], 'project');
        $this->setAttributeValue($obj, $result['done'], 'done');
        $this->setAttributeValue($obj, $result['status'], 'status');
        $this->setAttributeValue($obj, $result['description'], 'description');
        $this->setAttributeValue($obj, $result['subordinated_to'], 'subordinatedTo');
        $this->setAttributeValue($obj, $result['responsible'], 'responsible');
        $this->setAttributeValue($obj, $result['milestone'], 'milestone');
        $this->setAttributeValue($obj, $result['requirement_for_receiving'], 'requirementForReceiving');

        $this->identityMap[$obj] = $id;

        $this->fetchDates($obj);
        $check = new C3op_Projects_ActionCheckStart($obj, $this);
        return $obj;

    }

    public function delete(C3op_Projects_Action $obj)
    {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_ActionMapperException('Object has no ID, cannot delete.');
        }
        $query = $this->db->prepare('DELETE FROM projects_actions WHERE id = :id;');
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();
        $query = $this->db->prepare('DELETE FROM projects_actions_dates WHERE id = :id;');
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();

        unset($this->identityMap[$obj]);
    }

    public function getAllOtherActions(C3op_Projects_Action $a)
    {
        $query = $this->db->prepare('SELECT id FROM projects_actions WHERE project = :project AND id != :id;');
        $query->bindValue(':project', $a->GetProject(), PDO::PARAM_STR);
        $query->bindValue(':id', $a->GetId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getActionsSubordinatedTo(C3op_Projects_Action $a)
    {
        $query = $this->db->prepare('SELECT id FROM projects_actions WHERE subordinated_to = :subordinatedTo;');
        $query->bindValue(':subordinatedTo', $a->GetId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }

        return $result;

    }

    private function setAttributeValue(C3op_Projects_Action $a, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($a, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $fieldValue);
    }

    private function insertDates(C3op_Projects_Action $new)
    {
        $query = $this->db->prepare("INSERT INTO projects_actions_dates (action, predicted_begin_date, predicted_finish_date, real_begin_date, real_finish_date) VALUES (:action, :predicted_begin_date, :predicted_finish_date, :real_begin_date, :real_finish_date)");

        $query->bindValue(':action', $new->getId(), PDO::PARAM_STR);
        $query->bindValue(':predicted_begin_date', $new->GetPredictedBeginDate(), PDO::PARAM_STR);
        $query->bindValue(':predicted_finish_date', $new->GetPredictedFinishDate(), PDO::PARAM_STR);
        $query->bindValue(':real_begin_date', $new->GetRealBeginDate(), PDO::PARAM_STR);
        $query->bindValue(':real_finish_date', $new->GetRealFinishDate(), PDO::PARAM_STR);

        $query->execute();

    }

    private function fetchDates(C3op_Projects_Action $action)
    {

        $query = $this->db->prepare('SELECT predicted_begin_date, predicted_finish_date, real_begin_date, real_finish_date FROM projects_actions_dates WHERE action = :id;');
        $query->bindValue(':id', $action->GetId(), PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if (empty($result)) {
            $this->insertDates($action);
            $this->fetchDates($action);
            return;
        }
        $this->setAttributeValue($action, $result['predicted_begin_date'], 'predictedBeginDate');
        $this->setAttributeValue($action, $result['predicted_finish_date'], 'predictedFinishDate');
        $this->setAttributeValue($action, $result['real_begin_date'], 'realBeginDate');
        $this->setAttributeValue($action, $result['real_finish_date'], 'realFinishDate');

    }

   public function fetchLastReceiptDate(C3op_Projects_Action $action)
    {
        $query = $this->db->prepare('SELECT timestamp FROM projects_actions_events WHERE action = :action AND type = :type ORDER BY timestamp DESC LIMIT 1;');
        $query->bindValue(':action', $action->GetId(), PDO::PARAM_STR);
        $query->bindValue(':type', C3op_Projects_ActionEventConstants::EVENT_ACKNOWLEDGE_RECEIPT, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if (empty($result)) {
            $receiptDate = "0000-00-00";
        } else {
            //want only date, not full timestamp
            $dateAndTimeStamp = explode(" ", $result['timestamp']);
            $receiptDate = $dateAndTimeStamp[0];
        }

        $this->setAttributeValue($action, $receiptDate, 'receiptDate');
    }

   public function fetchLastDoneDate(C3op_Projects_Action $action)
    {
        $query = $this->db->prepare('SELECT timestamp FROM projects_actions_events WHERE action = :action AND type = :type ORDER BY timestamp DESC LIMIT 1;');
        $query->bindValue(':action', $action->GetId(), PDO::PARAM_STR);
        $query->bindValue(':type', C3op_Projects_ActionEventConstants::EVENT_CONFIRM_REALIZATION, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if (empty($result)) {
            $doneDate = null;
        } else {
            $doneDate = $result['timestamp'];
        }

        $this->setAttributeValue($action, $doneDate, 'doneDate');
    }

   public function getLastAutoStartDate(C3op_Projects_Action $action)
    {
        $query = $this->db->prepare('SELECT timestamp FROM projects_actions_events WHERE action = :action AND type = :type ORDER BY timestamp DESC LIMIT 1;');
        $query->bindValue(':action', $action->GetId(), PDO::PARAM_STR);
        $query->bindValue(':type', C3op_Projects_ActionEventConstants::EVENT_BEGIN_AUTOMATICALLY, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if (empty($result)) {
            $autoStartDate = null;
        } else {
            $autoStartDate = $result['timestamp'];
        }

        return $autoStartDate;
    }

   public function getLastAcknowledgeStartDate(C3op_Projects_Action $action)
    {
        $query = $this->db->prepare('SELECT timestamp FROM projects_actions_events WHERE action = :action AND type = :type ORDER BY timestamp DESC LIMIT 1;');
        $query->bindValue(':action', $action->GetId(), PDO::PARAM_STR);
        $query->bindValue(':type', C3op_Projects_ActionEventConstants::EVENT_BEGIN_ACKNOWLEDGMENT, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if (empty($result)) {
            $acknowledgeDate = null;
        } else {
            $acknowledgeDate = $result['timestamp'];
        }
        return ($acknowledgeDate);

    }

    public function getContractedValueJustForThisAction(C3op_Projects_Action $a)
    {
        $query = $this->db->prepare('SELECT SUM(value) as value FROM projects_team_members WHERE action = :action AND status = :status;');
        $query->bindValue(':action', $a->GetId(), PDO::PARAM_STR);
        $query->bindValue(':status', C3op_Projects_TeamMemberStatusConstants::STATUS_CONTRACTED, PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        foreach ($resultPDO as $row) {
            if (!is_null($row['value'])) {
                return $row['value'];
            } else {
                return 0;
            }
        }
        return 0;
    }

    public function getContractedValueForActionTree(C3op_Projects_Action $a)
    {
        $query = $this->db->prepare('SELECT id FROM projects_actions WHERE subordinated_to = :subordinatedTo;');
        $query->bindValue(':subordinatedTo', $a->GetId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $value = $this->getContractedValueJustForThisAction($a);
        foreach ($resultPDO as $row) {
            $childAction = $this->findById($row['id']);
            $value += $this->getContractedValueJustForThisAction($childAction);


        }
        return $value;
    }

    private function updateDates(C3op_Projects_Action $action)
    {
        $query = $this->db->prepare('UPDATE projects_actions_dates SET predicted_begin_date = :predictedBeginDate, predicted_finish_date = :predictedFinishDate, real_begin_date = :realBeginDate, real_finish_date = :realFinishDate WHERE action = :action;');

        $query->bindValue(':predictedBeginDate', $action->GetPredictedBeginDate(), PDO::PARAM_STR);
        $query->bindValue(':predictedFinishDate', $action->GetPredictedFinishDate(), PDO::PARAM_STR);
        $query->bindValue(':realBeginDate', $action->GetRealBeginDate(), PDO::PARAM_STR);
        $query->bindValue(':realFinishDate', $action->GetRealFinishDate(), PDO::PARAM_STR);
        $query->bindValue(':action', $this->identityMap[$action], PDO::PARAM_STR);

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Projects_ActionException("$sql failed");
        }

    }

    public function getContractedTeamMembers(C3op_Projects_Action $a)
    {

        $query = $this->db->prepare('SELECT id FROM projects_team_members WHERE action = :action AND status = :status;');
        $query->bindValue(':action', $a->GetId(), PDO::PARAM_STR);
        $query->bindValue(':status', C3op_Projects_TeamMemberStatusConstants::STATUS_CONTRACTED, PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }


}