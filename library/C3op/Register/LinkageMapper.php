<?php

class C3op_Register_LinkageMapper
{

    protected $db;
    protected $identityMap;

    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
            foreach ($this->db->query('SELECT id FROM register_linkages;') as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function insert(C3op_Register_Linkage $new) {
        $data = array(
            'contact' => $new->GetContact(),
            'institution' => $new->GetInstitution(),
            'department' => $new->GetDepartment(),
            'state' => $new->GetState(),
            'position' => $new->GetPosition(),
            );
        $this->db->insert('register_linkages', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();

        $this->insertPhoneNumbers($new);
    }

    public function update(C3op_Register_Linkage $obj) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Register_LinkageMapperException('Object has no ID, cannot update.');
        }
        $sql =
                sprintf(
                    'UPDATE register_linkages SET contact = %d,
                        institution =  %d,
                        department =  \'%s\',
                        state =  \'%s\',
                        position =  \'%s\'
                         WHERE id = %d;',
                    $obj->GetContact(),
                    $obj->GetInstitution(),
                    $obj->GetDepartment(),
                    $obj->GetState(),
                    $obj->GetPosition(),
                    $this->identityMap[$obj]
                );
        try {
            $this->db->exec($sql);
        } catch (Exception $e) {
            throw new C3op_Register_LinkageException("$sql failed");
        }

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
                'SELECT contact, institution, department, state, position
                     FROM register_linkages WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Register_LinkageMapperException(sprintf('There is no Linkage with id #%d.', $id));
        }
        $obj = new C3op_Register_Linkage();

        $this->setAttributeValue($obj, $id, 'id');
        $this->setAttributeValue($obj, $result['contact'], 'contact');
        $this->setAttributeValue($obj, $result['institution'], 'institution');
        $this->setAttributeValue($obj, $result['department'], 'department');
        $this->setAttributeValue($obj, $result['state'], 'state');
        $this->setAttributeValue($obj, $result['position'], 'position');

        $this->identityMap[$obj] = $id;

        $phoneNumbers = $this->findPhoneNumbers($obj);
        $this->setAttributeValue($obj, $phoneNumbers, 'phoneNumbers');


        return $obj;

    }

    public function findByPhoneId($phoneId) {
        $result = $this->db->fetchRow(
            sprintf(
                'SELECT linkage FROM register_linkages_phone_numbers WHERE id = %d;',
                $phoneId
            )
        );
        if (empty($result)) {
            throw new C3op_Register_LinkageMapperException(sprintf('There is no linkage with a phone with this phone id #%d.', $phoneId));
        }
        return $this->findById($result['linkage']);
    }

    public function delete(C3op_Register_Linkage $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Register_LinkageMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM register_linkages WHERE id = %d;',
                $this->identityMap[$i]
            )
        );
        unset($this->identityMap[$i]);
    }

    private function setAttributeValue(C3op_Register_Linkage $i, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($i, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($i, $fieldValue);
    }

    private function insertPhoneNumbers(C3op_Register_Linkage $new)
    {
        foreach($new->GetPhoneNumbers() as $phoneNumber) {
            $data = array(
                'linkage' => $new->GetId(),
                'area_code' => $phoneNumber->GetAreaCode(),
                'local_number' => $phoneNumber->GetLocalNumber(),
                'label' => $phoneNumber->GetLabel(),
                );
            $this->db->insert('register_linkages_phone_numbers', $data);
        }
    }

    private function findPhoneNumbers(C3op_Register_Linkage $linkage)
    {
        $phoneNumbersArray = array();
        if ($linkage->GetId() > 0) {
            foreach ($this->db->query(sprintf(
                    'SELECT id, area_code, local_number, label FROM register_linkages_phone_numbers WHERE linkage = %d;',
                    $linkage->GetId()
                )
                    ) as $row) {
                $phoneNumber = new C3op_Register_LinkagePhoneNumber($row['id'], $row['area_code'], $row['local_number'], $row['label']);
                $phoneNumbersArray[$row['id']] = $phoneNumber;
               }
            return $phoneNumbersArray;
        } else {
            throw new C3op_Register_LinkageMapperException('Can\'t fetch phone numbers for a linkage that wasn\'t saved');
        }
    }

    private function UpdatePhoneNumbers(C3op_Register_Linkage $linkage)
    {
        $currentPhoneNumbers = $linkage->GetPhoneNumbers();
        $oldPhoneNumbers = $this->findPhoneNumbers($linkage);
        foreach($oldPhoneNumbers as $key =>$phoneNumber){
            if (isset($currentPhoneNumbers[$key])) {
                $newPhoneNumber = $currentPhoneNumbers[$key];
                if ($newPhoneNumber != $phoneNumber) {
                    $this->db->exec(
                    sprintf(
                        'UPDATE register_linkages_phone_numbers SET area_code = \'%s\', local_number = \'%s\', label = \'%s\' WHERE id = %d;',
                            $newPhoneNumber->GetAreaCode(),
                            $newPhoneNumber->GetLocalNumber(),
                            $newPhoneNumber->GetLabel(),
                            $key
                        )
                    );
                }
                unset($currentPhoneNumbers[$key]);
            } else {
                $this->db->exec(
                sprintf(
                    'DELETE FROM register_linkages_phone_numbers WHERE id = %d;',
                        $key
                    )
                );
            }

        }
        reset ($currentPhoneNumbers);
        foreach($currentPhoneNumbers as $key =>$phoneNumber){
            $data = array(
                'linkage' => $linkage->GetId(),
                'area_code' => $phoneNumber->GetAreaCode(),
                'local_number' => $phoneNumber->GetLocalNumber(),
                'label' => $phoneNumber->GetLabel(),
                );
            $this->db->insert('register_linkages_phone_numbers', $data);
        }

    }

   private function insertEmails(C3op_Register_Linkage $new)
    {
        foreach($new->GetEmails() as $email) {
            $data = array(
                'linkage' => $new->GetId(),
                'email' => $email->GetEmail(),
                'label' => $email->GetLabel(),
                );
            $this->db->insert('register_linkages_emails', $data);
        }
    }

    private function findEmails(C3op_Register_Linkage $linkage)
    {
        $emailsArray = array();
        if ($linkage->GetId() > 0) {
            foreach ($this->db->query(sprintf(
                    'SELECT id, email, label FROM register_linkages_emails WHERE linkage = %d;',
                    $linkage->GetId()
                )
                    ) as $row) {
                $email = new C3op_Register_LinkageEmail($row['id'], $row['email'], $row['label']);
                $emailsArray[$row['id']] = $email;
               }
            return $emailsArray;
        } else {
            throw new C3op_Register_LinkageMapperException('Can\'t fetch emails for a linkage that wasn\'t saved');
        }
    }

    private function UpdateEmails(C3op_Register_Linkage $linkage)
    {
        $currentEmails = $linkage->GetEmails();
        $oldEmails = $this->findEmails($linkage);
        foreach($oldEmails as $key =>$email){
            if (isset($currentEmails[$key])) {
                $newEmail = $currentEmails[$key];
                if ($newEmail != $email) {
                    $this->db->exec(
                    sprintf(
                        'UPDATE register_linkages_emails SET email = \'%s\', label = \'%s\' WHERE id = %d;',
                            $newEmail->GetEmail(),
                            $newEmail->GetLabel(),
                            $key
                        )
                    );
                }
                unset($currentEmails[$key]);
            } else {
                $this->db->exec(
                sprintf(
                    'DELETE FROM register_linkages_emails WHERE id = %d;',
                        $key
                    )
                );
            }

        }
        reset ($currentEmails);
        foreach($currentEmails as $key =>$email){
            $data = array(
                'linkage' => $linkage->GetId(),
                'email' => $email->GetEmail(),
                'label' => $email->GetLabel(),
                );
            $this->db->insert('register_linkages_emails', $data);
        }

    }

}