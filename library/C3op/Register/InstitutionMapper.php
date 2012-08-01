<?php

class C3op_Register_InstitutionMapper
{
    
    protected $db;
    protected $identityMap;
	
    function __construct() {
        $this->db = Zend_Registry::get('db');
        $this->identityMap = new SplObjectStorage;
    }

    public function getAllIds() {
        $result = array();
            foreach ($this->db->query('SELECT id FROM register_institutions;') as $row) {
            $result[] = $row['id'];
        }        
        return $result;
    }
    
    public function insert(C3op_Register_Institution $new) {
        $data = array(
            'name' => $new->GetName(),
            'short_name' => $new->GetShortName(),
            'legal_entity' => $new->GetLegalEntity(),
            'register_number' => $new->GetRegisterNumber(),
            'state_registration' => $new->GetStateRegistration(),
            'local_register_number' => $new->GetLocalRegisterNumber(),
            'street' => $new->GetStreet(),
            'street_number' => $new->GetStreetNumber(),
            'address_complement' => $new->GetAddressComplement(),
            'zip_code' => $new->GetZipCode(),
            'district' => $new->GetDistrict(),
            'city' => $new->GetCity(),
            'state' => $new->GetState(),
            'website' => $new->GetWebsite(),
            'type' => $new->GetType(),
            'relationship_type' => $new->GetRelationshipType(),
            );
        $this->db->insert('register_institutions', $data);
        $new->SetId((int)$this->db->lastInsertId());
        $this->identityMap[$new] = $new->GetId();
        
    }
    
    public function update(C3op_Register_Institution $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Register_InstitutionMapperException('Object has no ID, cannot update.');
        }
        $sql = 
                sprintf(
                    'UPDATE register_institutions SET name = \'%s\', 
                        short_name =  \'%s\',
                        legal_entity = %d,
                        register_number =  \'%s\',
                        state_registration =  \'%s\',
                        local_register_number =  \'%s\',
                        street =  \'%s\',
                        street_number =  \'%s\',
                        address_complement =  \'%s\',
                        zip_code =  \'%s\',
                        district =  \'%s\',
                        city =  \'%s\',
                        state =  \'%s\',
                        website =  \'%s\',
                        type = %d,
                        relationship_type = %d
                        WHERE id = %d;',
                    $i->GetName(),
                    $i->GetShortName(),
                    $i->GetLegalEntity(),
                    $i->GetRegisterNumber(),
                    $i->GetStateRegistration(),
                    $i->GetLocalRegisterNumber(),
                    $i->GetStreet(),
                    $i->GetStreetNumber(),
                    $i->GetAddressComplement(),
                    $i->GetZipCode(),
                    $i->GetDistrict(),
                    $i->GetCity(),
                    $i->GetState(),
                    $i->GetWebsite(),
                    $i->GetType(),
                    $i->GetRelationshipType(),
                    $this->identityMap[$i]
                );
        try {
            $this->db->exec($sql);
        } catch (Exception $e) {
            throw new C3op_Register_InstitutionException("$sql failed");
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
        
        $result = $this->db->fetchRow(
            sprintf(
                'SELECT name, short_name, legal_entity, register_number, state_registration,
                     local_register_number, street, street_number, address_complement, zip_code, district,
                    city, state, website, type, relationship_type FROM register_institutions WHERE id = %d;',
                $id
            )
        );
        if (empty($result)) {
            throw new C3op_Register_InstitutionMapperException(sprintf('There is no Institution with id #%d.', $id));
        }
        $i = new C3op_Register_Institution();
        
        $this->setAttributeValue($i, $id, 'id');
        $this->setAttributeValue($i, $result['name'], 'name');
        $this->setAttributeValue($i, $result['short_name'], 'shortName');
        $this->setAttributeValue($i, $result['legal_entity'], 'legalEntity');
        $this->setAttributeValue($i, $result['register_number'], 'registerNumber');
        $this->setAttributeValue($i, $result['state_registration'], 'stateRegistration');
        $this->setAttributeValue($i, $result['local_register_number'], 'localRegisterNumber');
        $this->setAttributeValue($i, $result['street'], 'street');
        $this->setAttributeValue($i, $result['street_number'], 'streetNumber');
        $this->setAttributeValue($i, $result['address_complement'], 'addressComplement');
        $this->setAttributeValue($i, $result['zip_code'], 'zipCode');
        $this->setAttributeValue($i, $result['district'], 'district');
        $this->setAttributeValue($i, $result['city'], 'city');
        $this->setAttributeValue($i, $result['state'], 'state');
        $this->setAttributeValue($i, $result['website'], 'website');
        $this->setAttributeValue($i, $result['type'], 'type');
        $this->setAttributeValue($i, $result['relationship_type'], 'relationshipType');

        $this->identityMap[$i] = $id;
        return $i;        

    }

    public function delete(C3op_Register_Institution $i) {
        if (!isset($this->identityMap[$i])) {
            throw new C3op_Register_InstitutionMapperException('Object has no ID, cannot delete.');
        }
        $this->db->exec(
            sprintf(
                'DELETE FROM register_institutions WHERE id = %d;',
                $this->identityMap[$i]
            )
        );
        unset($this->identityMap[$i]);
    }

    private function setAttributeValue(C3op_Register_Institution $i, $fieldValue, $attributeName)
    {
        $attribute = new ReflectionProperty($i, $attributeName);
        $attribute->setAccessible(TRUE);
        $attribute->setValue($i, $fieldValue);
    }

    public function getAllPossibleClients() {
        $result = array();
        foreach ($this->db->query(
            sprintf(
                'SELECT id FROM register_institutions WHERE relationship_type <> %d;'
                , C3op_Register_InstitutionRelationshipConstants::RELATIONSHIP_CONTRACTING
            )
        )as $row) {
            $result[] = $row['id'];
        }        
        return $result;
    }
    
    public function getAllLinkages(C3op_Register_Institution $i)
    {
        $result = array();
        foreach ($this->db->query(
                sprintf(
                    'SELECT id FROM register_linkages WHERE institution = %d;',
                    $i->GetId()
                    )
                )
                as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    
    
    
}