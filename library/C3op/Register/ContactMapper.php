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

    public function update(C3op_Register_Contact $obj) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Register_ContactMapperException('Object has no ID, cannot update.');
        }
        $this->db->exec(
            sprintf(
                'UPDATE register_contacts SET name = \'%s\', type = %d WHERE id = %d;',
                $obj->GetName(),
                $obj->GetType(),
                $this->identityMap[$obj]
            )
        );

        $this->updatePhoneNumbers($obj);

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
        $obj = new C3op_Register_Contact();

        $this->setAttributeValue($obj, $id, 'id');
        $this->setAttributeValue($obj, $result['name'], 'name');
        $this->setAttributeValue($obj, $result['type'], 'type');

        $this->identityMap[$obj] = $id;

        $phoneNumbers = $this->fetchPhoneNumbers($obj);
        $this->setAttributeValue($obj, $phoneNumbers, 'phoneNumbers');

        return $obj;

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
        foreach($new->GetPhoneNumbers() as $phoneNumber) {
            $data = array(
                'contact' => $new->GetId(),
                'area_code' => $phoneNumber['areaCode'],
                'local_number' => $phoneNumber['localNumber'],
                'label' => $phoneNumber['label'],
                );
            $this->db->insert('register_contacts_phone_numbers', $data);
        }
    }

    private function fetchPhoneNumbers(C3op_Register_Contact $contact)
    {
        $result = array();
        if ($contact->GetId() > 0) {
            foreach ($this->db->query(sprintf(
                    'SELECT id, area_code, local_number, label FROM register_contacts_phone_numbers WHERE contact = %d;',
                    $contact->GetId()
                )
                    ) as $row) {
                $result[$row['id']] = array(
                    'area_code' => $row['area_code'],
                    'local_number' => $row['local_number'],
                    'label' => $row['label'],
                    );
            }
            return $result;
        } else {
            throw new C3op_Register_ContactMapperException('Can\'t fetch phone numbers for a contact that wasn\'t saved');
        }
    }

    private function UpdatePhoneNumbers(C3op_Register_Contact $contact)
    {

        $currentPhoneNumbers = $contact->GetPhoneNumbers();
        $oldPhoneNumbers = $this->fetchPhoneNumbers($contact);

        foreach($oldPhoneNumbers as $key =>$phoneNumber){
            if (isset($currentPhoneNumbers[$key])) {
                $newPhoneNumber = $currentPhoneNumbers[$key];
                if ($newPhoneNumber != $phoneNumber) {

                    $this->db->exec(
                    sprintf(
                        'UPDATE register_contacts_phone_numbers SET area_code = \'%s\', local_number = \'%s\', label = \'%s\' WHERE id = %d;',
                            $newPhoneNumber['area_code'],
                            $newPhoneNumber['local_number'],
                            $newPhoneNumber['label'],
                            $key
                        )
                    );
                }
                unset($currentPhoneNumbers[$key]);
            } else {
                    $this->db->exec(
                    sprintf(
                        'DELETE FROM register_contacts_phone_numbers WHERE id = %d;',
                            $key
                        )
                    );

            }

        }
        reset ($currentPhoneNumbers);

        foreach($currentPhoneNumbers as $key =>$phoneNumber){
            $data = array(
                'contact' => $contact->GetId(),
                'area_code' => $phoneNumber['area_code'],
                'local_number' => $phoneNumber['local_number'],
                'label' => $phoneNumber['label'],
                );
            $this->db->insert('register_contacts_phone_numbers', $data);
        }

    }



}