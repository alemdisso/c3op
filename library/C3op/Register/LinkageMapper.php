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

        $query = $this->db->prepare('SELECT id FROM register_linkages WHERE 1=1;');
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function insert(C3op_Register_Linkage $obj) {

        $query = $this->db->prepare("INSERT INTO register_linkages (contact, institution, department, position) VALUES (:contact, :institution, :department, :position)");
        $query->bindValue(':contact', $obj->GetContact(), PDO::PARAM_STR);
        $query->bindValue(':institution', $obj->GetInstitution(), PDO::PARAM_STR);
        $query->bindValue(':department', $obj->GetDepartment(), PDO::PARAM_STR);
        $query->bindValue(':position', $obj->GetPosition(), PDO::PARAM_STR);
        $query->execute();

        $obj->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$obj] = $obj->GetId();

        $this->insertPhoneNumbers($obj);
        $this->insertEmails($obj);
    }

    public function update(C3op_Register_Linkage $obj) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Register_LinkageMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE register_linkages
            SET contact = :contact, institution = :institution , department = :department, position = :position WHERE id = :id;");

        $query->bindValue(':contact', $obj->GetContact(), PDO::PARAM_STR);
        $query->bindValue(':institution', $obj->GetInstitution(), PDO::PARAM_STR);
        $query->bindValue(':department', $obj->GetDepartment(), PDO::PARAM_STR);
        $query->bindValue(':position', $obj->GetPosition(), PDO::PARAM_STR);
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Register_ContactException("sql failed");
        }

        $this->updatePhoneNumbers($obj);
        $this->updateEmails($obj);
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
                'SELECT contact, institution, department, position
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
        $this->setAttributeValue($obj, $result['position'], 'position');

        $this->identityMap[$obj] = $id;

        $phoneNumbers = $this->findPhoneNumbers($obj);
        $this->setAttributeValue($obj, $phoneNumbers, 'phoneNumbers');

        $emails = $this->findEmails($obj);
        $this->setAttributeValue($obj, $emails, 'emails');


        return $obj;

    }

    public function findByContactAndInstitution($contact, $institution) {
        $result = $this->db->fetchRow(
            sprintf(
                'SELECT id FROM register_linkages WHERE contact = %d AND institution = %d ;',
                $contact,
                $institution
            )
        );
        if (empty($result)) {
            //throw new C3op_Register_LinkageMapperException(sprintf('There is no linkage for this contact $s and institution %s.', $contact, $institution));
            return null;
        }
        return $this->findById($result['id']);
    }

    public function findByPhoneId($phoneId) {
        $result = $this->db->fetchRow(
            sprintf(
                'SELECT linkage FROM register_linkages_phone_numbers WHERE id = %d;',
                $phoneId
            )
        );
        if (empty($result)) {
            return null;
            //throw new C3op_Register_LinkageMapperException(sprintf('There is no linkage with a phone with this phone id #%d.', $phoneId));
        }
        return $this->findById($result['linkage']);
    }

    public function delete(C3op_Register_Linkage $obj) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Register_LinkageMapperException('Object has no ID, cannot delete.');
        }

        $query = $this->db->prepare('DELETE FROM register_linkages WHERE id = :id;');
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();

        $query = $this->db->prepare('DELETE FROM register_linkages_emails WHERE linkage = :linkage;');
        $query->bindValue(':linkage', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();

        $query = $this->db->prepare('DELETE FROM register_linkages_phone_numbers WHERE linkage = :linkage;');
        $query->bindValue(':linkage', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();

        unset($this->identityMap[$obj]);
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

    private function updateEmails(C3op_Register_Linkage $linkage)
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