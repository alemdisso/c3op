<?php

class C3op_Projects_ActionEventMapper
{

    protected $db;
    protected $action;
    protected $identityMap;

    function __construct(C3op_Projects_Action $action)
    {
        $this->action = $action;
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds()
    {

        $query = $this->db->prepare('SELECT id FROM projects_actions_events WHERE action = :action;');
        $query->bindValue(':action', $this->action->GetId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }

    public function insert(C3op_Projects_ActionEvent $obj)
    {

        $query = $this->db->prepare("INSERT INTO projects_actions_events (action, type, timestamp, observation, responsible) VALUES (:action, :type, :timestamp, :observation, :responsible)");

        $query->bindValue(':action',      $this->action->GetId(), PDO::PARAM_STR);
        $query->bindValue(':type',        $obj->GetType(), PDO::PARAM_STR);
        $query->bindValue(':timestamp',   date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $query->bindValue(':observation', $obj->GetObservation(), PDO::PARAM_STR);
        $query->bindValue(':responsible', $obj->GetResponsible(), PDO::PARAM_STR);

        $query->execute();

        $obj->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$obj] = $obj->GetId();
    }

    public function update(C3op_Projects_ActionEvent $obj)
    {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_ActionEventMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE projects_actions_events SET action = :action, type = :type, timestamp = :timestamp, observation = :observation, responsible = :responsible WHERE id = :id;");

        $query->bindValue(':action', $obj->GetAction(), PDO::PARAM_STR);
        $query->bindValue(':type', $obj->GetType(), PDO::PARAM_STR);
        $query->bindValue(':timestamp', $obj->GetTimestamp(), PDO::PARAM_STR);
        $query->bindValue(':observation', $obj->GetObservation(), PDO::PARAM_STR);
        $query->bindValue(':responsible', $obj->GetResponsible(), PDO::PARAM_STR);
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);

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

        $query = $this->db->prepare('SELECT action, type, timestamp, observation, responsible FROM projects_actions WHERE id = :id;');
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if (empty($result)) {
            throw new C3op_Projects_ActionEventMapperException(sprintf('There is no action\'s event with id #%d.', $id));
        }
        $action = $result['action'];

        $obj = new C3op_Projects_ActionEvent($action);
        $this->setAttributeValue($obj, $id, 'id');
        $this->setAttributeValue($obj, $result['action'], 'action');
        $this->setAttributeValue($obj, $result['type'], 'type');
        $this->setAttributeValue($obj, $result['timestamp'], 'timestamp');
        $this->setAttributeValue($obj, $result['observation'], 'observation');
        $this->setAttributeValue($obj, $result['responsible'], 'responsible');


        $this->identityMap[$obj] = $id;

        return $obj;

    }

    public function delete(C3op_Projects_ActionEvent $obj)
    {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Projects_ActionEventMapperException('Object has no ID, cannot delete.');
        }

        $query = $this->db->prepare('DELETE FROM projects_actions_events WHERE id = :id;');
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();

        unset($this->identityMap[$obj]);
    }

}