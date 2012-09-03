<?php

class C3op_Register_ContactMessengerMapper {

    protected $db;
    protected $identityMap;

    function __construct($db, $identityMap) {
        $this->db = $db;
        $this->identityMap = $identityMap;
    }

    public function insertMessengers(C3op_Register_Contact $new)
    {
        foreach($new->GetMessengers() as $messenger) {
            $data = array(
                'contact' => $new->GetId(),
                'address' => $messenger->GetAddress(),
                'service' => $messenger->GetService(),
                );
            $this->db->insert('register_contacts_messengers', $data);
        }
    }

    public function findMessengers(C3op_Register_Contact $contact)
    {
        $messengersArray = array();
        if ($contact->GetId() > 0) {
            foreach ($this->db->query(sprintf(
                    'SELECT id, address, service FROM register_contacts_messengers WHERE contact = %d;',
                    $contact->GetId()
                )
                    ) as $row) {
                $messenger = new C3op_Register_ContactMessenger($row['id'], $row['address'], $row['service']);
                $messengersArray[$row['id']] = $messenger;
               }
            return $messengersArray;
        } else {
            throw new C3op_Register_ContactMapperException('Can\'t fetch service messengers for a contact that wasn\'t saved');
        }
    }

    public function UpdateMessengers(C3op_Register_Contact $contact)
    {
        $currentMessengers = $contact->GetMessengers();
        $oldMessengers = $this->findMessengers($contact);
        foreach($oldMessengers as $key =>$messenger){
            if (isset($currentMessengers[$key])) {
                $newMessenger = $currentMessengers[$key];
                if ($newMessenger != $messenger) {
                    $this->db->exec(
                    sprintf(
                        'UPDATE register_contacts_messengers SET address = \'%s\', service = \'%s\' WHERE id = %d;',
                            $newMessenger->GetAddress(),
                            $newMessenger->GetService(),
                            $key
                        )
                    );
                }
                unset($currentMessengers[$key]);
            } else {
                $this->db->exec(
                sprintf(
                    'DELETE FROM register_contacts_messengers WHERE id = %d;',
                        $key
                    )
                );
            }

        }
        reset ($currentMessengers);
        foreach($currentMessengers as $key =>$messenger){
            $data = array(
                'contact' => $contact->GetId(),
                'address' => $messenger->GetAddress(),
                'service' => $messenger->GetService(),
                );
            $this->db->insert('register_contacts_messengers', $data);
        }

    }

}

