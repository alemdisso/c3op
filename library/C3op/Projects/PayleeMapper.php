<?php

class C3op_Projects_PayleeMapper
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
        foreach ($this->db->query('SELECT id FROM projects_paylees;') as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function insert(C3op_Projects_Paylee $new)
    {
        $data = array(
            'project' => $new->GetProject(),
            'action' => $new->GetAction(),
            'team_member' => $new->GetTeamMember(),
            'outside_service' => $new->GetOutsideService(),
            'material_supply' => $new->GetMaterialSupply(),
            );
        $this->db->insert('projects_paylees', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();

    }

    public function update(C3op_Projects_Paylee $o)
    {
        if (!isset($this->identityMap[$o])) {
            throw new C3op_Projects_PayleeMapperException('Object has no ID, cannot update.');
        }

        $this->db->exec(
            sprintf(
                'UPDATE projects_paylees SET project = %d, action = %d, team_member = %d, outside_service = %d, material_supply = %d WHERE id = %d;',
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
                'SELECT  project, action, team_member, outside_service, material_supply, real_value, real_date, recurrent, observation FROM finances_outlays WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Projects_PayleeMapperException(sprintf('There is no outlay with id #%d.', $id));
        }
        $r = new C3op_Projects_Paylee($result['team_member'], $id);
        $this->setAttributeValue($r, $id, 'id');
        $this->setAttributeValue($r, $result['project'], 'project');
        $this->setAttributeValue($r, $result['action'], 'action');
        $this->setAttributeValue($r, $result['outside_service'], 'outsideService');
        $this->setAttributeValue($r, $result['material_supply'], 'materialSupply');

        $this->identityMap[$r] = $id;
        return $r;

    }

    public function delete(C3op_Projects_Paylee $a)
    {
        if (!isset($this->identityMap[$a])) {
            throw new C3op_Projects_PayleeMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM projects_paylees WHERE id = %d;',
                $this->identityMap[$a]
            )
        );
        unset($this->identityMap[$a]);
    }


    private function setAttributeValue(C3op_Projects_Paylee $a, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($a, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($a, $fieldValue);
    }

     public function getAllPayleesForTeamMember(C3op_Projects_TeamMember $obj) {
        $result = array();
            foreach ($this->db->query(
                    sprintf('SELECT id FROM projects_paylees WHERE team_member = %d;', $obj->GetId())) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

     public function getAllPayleesForOutsideService(C3op_Projects_OutsideService $obj) {
        $result = array();
            foreach ($this->db->query(
                    sprintf('SELECT id FROM projects_paylees WHERE outside_service = %d;', $obj->GetId())) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

     public function getAllPayleesForMaterialSupply(C3op_Projects_MaterialSupply $obj) {
        $result = array();
            foreach ($this->db->query(
                    sprintf('SELECT id FROM projects_paylees WHERE material_supply = %d;', $obj->GetId())) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }



}