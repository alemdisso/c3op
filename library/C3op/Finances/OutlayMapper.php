<?php

class C3op_Finances_OutlayMapper
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
        $result = array();
        foreach ($this->db->query('SELECT id FROM finances_outlays;') as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function insert(C3op_Finances_Outlay $new)
    {
        $data = array(
            'project' => $new->GetProject(),
            'action' => $new->GetAction(),
            'team_member' => $new->GetTeamMember(),
            'predicted_value' => $new->GetPredictedValue(),
            'predicted_date' => $new->GetPredictedDate(),
            'real_value' => $new->GetRealValue(),
            'real_date' => $new->GetRealDate(),
            'recurrent' => $new->GetRecurrent(),
            'observation' => $new->GetObservation(),
            );
        $this->db->insert('finances_outlays', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();

    }

    public function update(C3op_Finances_Outlay $o)
    {
        if (!isset($this->identityMap[$o])) {
            throw new C3op_Finances_OutlayMapperException('Object has no ID, cannot update.');
        }

        $this->db->exec(
            sprintf(
                'UPDATE finances_outlays SET project = %d, action = %d, team_member = %d, predicted_value = %.2f, predicted_date = \'%s\', real_value = %.2f, real_date = \'%s\', recurrent = %d, observation = \'%s\' WHERE id = %d;',
                $o->GetProject(),
                $o->GetAction(),
                $o->GetTeamMember(),
                $o->GetPredictedValue(),
                $o->GetPredictedDate(),
                $o->GetRealValue(),
                $o->GetRealDate(),
                $o->GetRecurrent(),
                $o->GetObservation(),
                $this->identityMap[$o]
            )
        );

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

        $result = $this->db->fetchRow(
            sprintf(
                'SELECT  project, action, team_member, predicted_value, predicted_date, real_value, real_date, recurrent, observation FROM finances_outlays WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Finances_OutlayMapperException(sprintf('There is no outlay with id #%d.', $id));
        }
        $r = new C3op_Finances_Outlay($result['team_member'], $id);
        $this->setAttributeValue($r, $id, 'id');
        $this->setAttributeValue($r, $result['project'], 'project');
        $this->setAttributeValue($r, $result['action'], 'action');
        $this->setAttributeValue($r, $result['predicted_value'], 'predictedValue');
        $this->setAttributeValue($r, $result['predicted_date'], 'predictedDate');
        $this->setAttributeValue($r, $result['real_value'], 'realValue');
        $this->setAttributeValue($r, $result['real_date'], 'realDate');
        $this->setAttributeValue($r, $result['recurrent'], 'recurrent');
        $this->setAttributeValue($r, $result['observation'], 'observation');

        $this->identityMap[$r] = $id;
        return $r;

    }

    public function delete(C3op_Finances_Outlay $a)
    {
        if (!isset($this->identityMap[$a])) {
            throw new C3op_Finances_OutlayMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM finances_outlays WHERE id = %d;',
                $this->identityMap[$a]
            )
        );
        unset($this->identityMap[$a]);
    }


    private function setAttributeValue(C3op_Finances_Outlay $a, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($a, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $fieldValue);
    }

     public function getAllOutlaysForTeamMember(C3op_Projects_TeamMember $h) {
        $result = array();
            foreach ($this->db->query(
                    sprintf('SELECT id FROM finances_outlays WHERE team_member = %d;', $h->GetId())) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }



}