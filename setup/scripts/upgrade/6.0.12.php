<?php

declare(strict_types=1);
$addresses = $db->select('CubeCart_addressbook', ['state', 'address_id']);
if ($addresses) {
    foreach ($addresses as $address) {
        if (strlen($address['state']) == 2 && !is_numeric($address['state'])) {
            $state = strtoupper($address['state']);
            $match = $db->select('CubeCart_geo_zone', ['id'], ['abbrev' => $state]);
            if ($match && $match[0]['id'] > 0) {
                $db->update('CubeCart_addressbook', ['state' => $match[0]['id']], ['address_id' => $address['address_id']]);
            }
        } elseif (!is_numeric($address['state'])) {
            $state = $address['state'];
            $match = $db->select('CubeCart_geo_zone', ['id'], ['name' => $state]);
            if ($match && $match[0]['id'] > 0) {
                $db->update('CubeCart_addressbook', ['state' => $match[0]['id']], ['address_id' => $address['address_id']]);
            }
        }
    }
}
