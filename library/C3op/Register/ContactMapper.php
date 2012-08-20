<?php

class C3op_Register_ContactMapper
{

    protected $db;
    protected $identityMap;

    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
        foreach ($this->db->query('SELECT id FROM register_contacts;') as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function insert(C3op_Register_Contact $new) {
        $data = array(
            'name' => $new->GetName(),
            'type' => $new->GetType()
            );
        $this->db->insert('register_contacts', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();

        $this->insertPhoneNumbers($new);

    }

    public function update(C3op_Register_Contact $c) {
        if (!isset($this->identityMap[$c])) {
            throw new C3op_Register_ContactMapperException('Object has no ID, cannot update.');
        }
        $this->db->exec(
            sprintf(
                'UPDATE register_contacts SET name = \'%s\', type = %d WHERE id = %d;',
                $c->GetName(),
                $c->GetType(),
                $this->identityMap[$c]
            )
        );

    }

    public function findById($id) {
        $this->identityMap->rewind();
        while ($this->identityMap->valid()) {
            if ($this->identityMap->getInfo() == $id) {
                return $this->identityMap->current();
            }
            $this->identityMap->next();
        }

        $result = $this->db->fetchRow(
            sprintf(
                'SELECT name, type FROM register_contacts WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Register_ContactMapperException(sprintf('There is no contact with id #%d.', $id));
        }
        $c = new C3op_Register_Contact();

        $this->setAttributeValue($c, $id, 'id');
        $this->setAttributeValue($c, $result['name'], 'name');
        $this->setAttributeValue($c, $result['type'], 'type');

        $this->identityMap[$c] = $id;
        return $c;

    }

    public function delete(C3op_Register_Contact $c) {
        if (!isset($this->identityMap[$c])) {
            throw new C3op_Register_ContactMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM register_contacts WHERE id = %d;',
                $this->identityMap[$c]
            )
        );
        unset($this->identityMap[$c]);
    }

    private function setAttributeValue(C3op_Register_Contact $c, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($c, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($c, $fieldValue);
    }

    public function getAllLinkages(C3op_Register_Contact $c)
    {
        $result = array();
        foreach ($this->db->query(
                sprintf(
                    'SELECT id FROM register_linkages WHERE contact = %d;',
                    $c->GetId()
                    )
                )
                as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllContactThatAreLinkedToAContractant() {
        $result = array();
        foreach ($this->db->query('SELECT c.id
                    FROM register_contacts c
                    INNER JOIN register_linkages l ON c.id = l.contact
                    INNER JOIN register_institutions i ON l.institution = i.id
                    WHERE i.relationship_type =' . C3op_Register_InstitutionRelationshipConstants::RELATIONSHIP_CONTRACTING
                    ) as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    private function insertPhoneNumbers(C3op_Register_Contact $new)
    {
        foreach($new->GetPhoneNumbers() as $phoneNumber)
            $data = array(
                'contact' => $phoneNumber['contact'],
                'area_code' => $phoneNumber['areaCode'],
                'local_number' => $phoneNumber['localNumber'],
                'label' => $phoneNumber['label'],
                );
            $this->db->insert('register_contacts_phone_numbers', $data);
    }

    private function setPhone(C3op_Projects_Action $action)
    {
        $result = $this->db->fetchRow(
            sprintf(
                'SELECT predicted_begin_date, predicted_finish_date, real_begin_date, real_finish_date FROM projects_actions_dates WHERE action = %d;',
                $action->GetId()
            )
        );

        if (empty($result)) {
            $this->insertDates($action);
            $this->setDates($action);
            return;
        }

        $this->setAttributeValue($action, $result['predicted_begin_date'], 'predictedBeginDate');
        $this->setAttributeValue($action, $result['predicted_finish_date'], 'predictedFinishDate');
        $this->setAttributeValue($action, $result['real_begin_date'], 'realBeginDate');
        $this->setAttributeValue($action, $result['real_finish_date'], 'realFinishDate');

    }

}