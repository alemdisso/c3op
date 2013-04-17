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
        $query = $this->db->prepare('SELECT id FROM register_contacts WHERE 1=1;');
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }

    public function insert(C3op_Register_Contact $obj) {

        $query = $this->db->prepare("INSERT INTO register_contacts (name, type) VALUES (:name, :type)");
        $query->bindValue(':name', $obj->GetName(), PDO::PARAM_STR);
        $query->bindValue(':type', $obj->GetType(), PDO::PARAM_STR);
        $query->execute();

        $obj->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$obj] = $obj->GetId();

        $this->insertPhoneNumbers($obj);
        $this->insertEmails($obj);
        $this->insertMessengers($obj);

    }

    public function update(C3op_Register_Contact $obj) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Register_ContactMapperException('Object has no ID, cannot update.');
        }

        $query = $this->db->prepare("UPDATE register_contacts SET name = :name, type = :type WHERE id = :id;");

        $query->bindValue(':name', $obj->GetName(), PDO::PARAM_STR);
        $query->bindValue(':type', $obj->GetType(), PDO::PARAM_STR);
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);

        try {
            $query->execute();
        } catch (Exception $e) {
            throw new C3op_Register_ContactException("sql failed");
        }

        $this->updatePhoneNumbers($obj);
        $this->updateEmails($obj);
        $this->updateMessengers($obj);

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

        $phoneNumbers = $this->findPhoneNumbers($obj);
        $this->setAttributeValue($obj, $phoneNumbers, 'phoneNumbers');

        $emails = $this->findEmails($obj);
        $this->setAttributeValue($obj, $emails, 'emails');

        $messengers = $this->findMessengers($obj);
        $this->setAttributeValue($obj, $messengers, 'messengers');

        return $obj;

    }

    public function findByPhoneId($phoneId) {
        $result = $this->db->fetchRow(
            sprintf(
                'SELECT contact FROM register_contacts_phone_numbers WHERE id = %d;',
                $phoneId
            )
        );
        if (empty($result)) {
            throw new C3op_Register_ContactMapperException(sprintf('There is no contact with a phone with this phone id #%d.', $phoneId));
        }
        return $this->findById($result['contact']);
    }

    public function findByEmailId($emailId) {
        $result = $this->db->fetchRow(
            sprintf(
                'SELECT contact FROM register_contacts_emails WHERE id = %d;',
                $emailId
            )
        );
        if (empty($result)) {
            throw new C3op_Register_ContactMapperException(sprintf('There is no contact with a email with this email id #%d.', $emailId));
        }
        return $this->findById($result['contact']);
    }

    public function findByMessengerId($messengerId) {
        $result = $this->db->fetchRow(
            sprintf(
                'SELECT contact FROM register_contacts_messengers WHERE id = %d;',
                $messengerId
            )
        );
        if (empty($result)) {
            throw new C3op_Register_ContactMapperException(sprintf('There is no contact with a messenger with this id #%d.', $messengerId));
        }
        return $this->findById($result['contact']);
    }

    public function delete(C3op_Register_Contact $obj) {
        if (!isset($this->identityMap[$obj])) {
            throw new C3op_Register_ContactMapperException('Object has no ID, cannot delete.');
        }

        $query = $this->db->prepare('DELETE FROM register_contacts WHERE id = :id;');
        $query->bindValue(':id', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();

        $query = $this->db->prepare('DELETE FROM register_contacts_emails WHERE contact = :contact;');
        $query->bindValue(':contact', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();

        $query = $this->db->prepare('DELETE FROM register_contacts_messengers WHERE contact = :contact;');
        $query->bindValue(':contact', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();

        $query = $this->db->prepare('DELETE FROM register_contacts_phone_numbers WHERE contact = :contact;');
        $query->bindValue(':contact', $this->identityMap[$obj], PDO::PARAM_STR);
        $query->execute();

        unset($this->identityMap[$obj]);
    }

    private function setAttributeValue(C3op_Register_Contact $c, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($c, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($c, $fieldValue);
    }

    public function getAllLinkages(C3op_Register_Contact $obj)
    {
        $query = $this->db->prepare('SELECT id FROM register_linkages WHERE contact = :id;');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function getAllContactsThatAreLinkedToAnyInstitution() {

        $query = $this->db->prepare('SELECT l.id as linkage, c.id as contact, c.name, i.id as institution, i.short_name
                    FROM register_contacts c
                    INNER JOIN register_linkages l ON c.id = l.contact
                    INNER JOIN register_institutions i ON l.institution = i.id
                    WHERE 1=1 ORDER BY name, contact, institution, short_name;');
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row;
        }
        return $result;

    }

    public function getAllContactThatAreLinkedToAContractant($orderBy = "") {


        if ($orderBy != "") {
            $orderBy = "ORDER BY $orderBy";
        }

        $query = $this->db->prepare("SELECT c.id, c.name as name
                    FROM register_contacts c
                    INNER JOIN register_linkages l ON c.id = l.contact
                    INNER JOIN register_institutions i ON l.institution = i.id
                    WHERE i.relationship_type = :relationship_type $orderBy;");
        $query->bindValue(':relationship_type', C3op_Register_InstitutionRelationshipConstants::RELATIONSHIP_CONTRACTING, PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;

    }

    public function anInstitutionLinkedTo(C3op_Register_Contact $obj)
    {
        $query = $this->db->prepare('SELECT l.institution
                    FROM register_linkages l
                    WHERE l.contact = :id LIMIT 1;');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);

        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['institution'];
        }

        return $result;

    }

    public function aLinkageFrom(C3op_Register_Contact $obj)
    {
        $query = $this->db->prepare('SELECT l.id
                    FROM register_linkages l
                    WHERE l.contact = :id LIMIT 1;');
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);

        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }

        return $result;

    }


    private function insertPhoneNumbers(C3op_Register_Contact $obj)
    {

        foreach($obj->GetPhoneNumbers() as $phoneNumber) {

            $query = $this->db->prepare("
                INSERT INTO register_contacts_phone_numbers
                (contact, area_code, local_number, label)
                VALUES (:contact, :area_code, :local_number, :label)");

            $query->bindValue(':contact', $obj->getId(), PDO::PARAM_STR);
            $query->bindValue(':area_code', $phoneNumber->GetAreaCode(), PDO::PARAM_STR);
            $query->bindValue(':local_number', $phoneNumber->GetLocalNumber(), PDO::PARAM_STR);
            $query->bindValue(':label', $phoneNumber->GetLabel(), PDO::PARAM_STR);

            $query->execute();

        }
    }

    private function findPhoneNumbers(C3op_Register_Contact $obj)
    {
        $phoneNumbersArray = array();
        if ($obj->GetId() > 0) {
            $query = $this->db->prepare('SELECT id, area_code, local_number, label FROM register_contacts_phone_numbers WHERE contact = :contact;');
            $query->bindValue(':contact', $obj->GetId(), PDO::PARAM_STR);
            $query->execute();
            $resultPDO = $query->fetchAll();

            $result = array();
            foreach ($resultPDO as $row) {
                $phoneNumber = new C3op_Register_ContactPhoneNumber($row['id'], $row['area_code'], $row['local_number'], $row['label']);
                $phoneNumbersArray[$row['id']] = $phoneNumber;
               }
            return $phoneNumbersArray;
        } else {
            throw new C3op_Register_ContactMapperException('Can\'t fetch phone numbers for a contact that wasn\'t saved');
        }
    }

    private function updatePhoneNumbers(C3op_Register_Contact $obj)
    {
        $currentPhoneNumbers = $obj->GetPhoneNumbers();
        $oldPhoneNumbers = $this->findPhoneNumbers($obj);
        foreach($oldPhoneNumbers as $key =>$phoneNumber){
            if (isset($currentPhoneNumbers[$key])) {
                $newPhoneNumber = $currentPhoneNumbers[$key];
                if ($newPhoneNumber != $phoneNumber) {

                    $query = $this->db->prepare('UPDATE register_contacts_phone_numbers SET area_code = :area_code, local_number = :local_number, label = :label WHERE id = :id;');

                    $query->bindValue(':area_code', $newPhoneNumber->GetAreaCode(), PDO::PARAM_STR);
                    $query->bindValue(':local_number', $newPhoneNumber->GetLocalNumber(), PDO::PARAM_STR);
                    $query->bindValue(':label', $newPhoneNumber->GetLabel(), PDO::PARAM_STR);
                    $query->bindValue(':id', $key, PDO::PARAM_STR);

                    try {
                        $query->execute();
                    } catch (Exception $e) {
                        throw new C3op_Register_ContactException("$sql failed");
                    }
                }
                unset($currentPhoneNumbers[$key]);
            } else {
                $query = $this->db->prepare('DELETE FROM register_contacts_phone_numbers WHERE id = :id;');
                $query->bindValue(':id', $key, PDO::PARAM_STR);
                $query->execute();
            }
        }

        reset ($currentPhoneNumbers);
        foreach($currentPhoneNumbers as $key =>$phoneNumber){

            $query = $this->db->prepare("INSERT INTO register_contacts_phone_numbers
                (contact, area_code, local_number, label)
                VALUES (:contact, :area_code, :local_number, :label)");

            $query->bindValue(':contact', $obj->getId(), PDO::PARAM_STR);
            $query->bindValue(':area_code', $phoneNumber->GetAreaCode(), PDO::PARAM_STR);
            $query->bindValue(':local_number', $phoneNumber->GetLocalNumber(), PDO::PARAM_STR);
            $query->bindValue(':label', $phoneNumber->GetLabel(), PDO::PARAM_STR);

            $query->execute();
        }
    }

   private function insertEmails(C3op_Register_Contact $obj)
    {
        foreach($obj->GetEmails() as $email) {

            $query = $this->db->prepare("
                INSERT INTO register_contacts_emails
                (contact, email, label)
                VALUES (:contact, :email, :label)");

            $query->bindValue(':contact', $obj->getId(), PDO::PARAM_STR);
            $query->bindValue(':email', $email->GetAddress(), PDO::PARAM_STR);
            $query->bindValue(':label', $email->GetLabel(), PDO::PARAM_STR);

            $query->execute();
        }
    }

    private function findEmails(C3op_Register_Contact $obj)
    {
        $emailsArray = array();
        if ($obj->GetId() > 0) {
            $query = $this->db->prepare('SELECT id, email, label FROM register_contacts_emails WHERE contact = :contact;');
            $query->bindValue(':contact', $obj->GetId(), PDO::PARAM_STR);
            $query->execute();
            $resultPDO = $query->fetchAll();

            $result = array();
            foreach ($resultPDO as $row) {
                $email = new C3op_Register_ContactEmail($row['id'], $row['email'], $row['label']);
                $emailsArray[$row['id']] = $email;
            }
            return $emailsArray;
        } else {
            throw new C3op_Register_ContactMapperException('Can\'t fetch emails for a contact that wasn\'t saved');
        }
    }

    private function updateEmails(C3op_Register_Contact $obj)
    {
        $currentEmails = $obj->GetEmails();
        $oldEmails = $this->findEmails($obj);
        foreach($oldEmails as $key =>$email){
            if (isset($currentEmails[$key])) {
                $newEmail = $currentEmails[$key];
                if ($newEmail != $email) {

                    $query = $this->db->prepare('UPDATE register_contacts_emails SET email = :email, label = :label WHERE id = :id;');

                    $query->bindValue(':email', $newEmail->GetAddress(), PDO::PARAM_STR);
                    $query->bindValue(':label', $newEmail->GetLabel(), PDO::PARAM_STR);
                    $query->bindValue(':id', $key, PDO::PARAM_STR);

                    try {
                        $query->execute();
                    } catch (Exception $e) {
                        throw new C3op_Register_ContactException("$sql failed");
                    }
                }
                unset($currentEmails[$key]);
            } else {
                $query = $this->db->prepare('DELETE FROM register_contacts_emails WHERE id = :id;');
                $query->bindValue(':id', $key, PDO::PARAM_STR);
                $query->execute();

            }
        }

        reset ($currentEmails);
        foreach($currentEmails as $key =>$email){
            $query = $this->db->prepare("INSERT INTO register_contacts_emails
                (contact, email, label)
                VALUES (:contact, :email, :label)");

            $query->bindValue(':contact', $obj->getId(), PDO::PARAM_STR);
            $query->bindValue(':email', $email->GetAddress(), PDO::PARAM_STR);
            $query->bindValue(':label', $email->GetLabel(), PDO::PARAM_STR);

            $query->execute();
        }

    }

     public function getAllActionsWithAContactAsResponsible(C3op_Register_Contact $obj) {


        $query = $this->db->prepare(
                'SELECT a.id
                    FROM projects_actions a
                    INNER JOIN resources_responsibles r ON a.id = r.action
                    WHERE r.contact = :id;'
                );
        $query->bindValue(':id', $obj->GetId(), PDO::PARAM_STR);
        $query->execute();
        $resultPDO = $query->fetchAll();

        $result = array();
        foreach ($resultPDO as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    private function insertMessengers(C3op_Register_Contact $obj)
    {
        $mapper = new C3op_Register_ContactMessengerMapper($this->db, $this->identityMap);
        $mapper->insertMessengers($obj);
    }

    private function findMessengers(C3op_Register_Contact $obj)
    {
        $mapper = new C3op_Register_ContactMessengerMapper($this->db, $this->identityMap);
        return $mapper->findMessengers($obj);
    }

    private function updateMessengers(C3op_Register_Contact $obj)
    {
        $mapper = new C3op_Register_ContactMessengerMapper($this->db, $this->identityMap);
        $mapper->updateMessengers($obj);
    }




}