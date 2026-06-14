<?php
class ZipLookup
{
    public static function makeSearchableUSZip($zipString)
    {
        return str_replace(' ', '', $zipString);
    }

    public function getCityStateByZip($zip)
    {
        return $this->lookupZip($zip);
    }

    public function lookupZip($zip)
    {
        $aAddress = array();
        $aAddress[0] = 0;
        $aAddress[1] = '';
        $aAddress[2] = '';
        $aAddress[3] = '';

        $loc_level_1 = '';
        $loc_level_2 = '';
        $loc_level_3 = '';
        $loc_level_4 = '';

        $sUrl = 'https://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=';

        if ($zip != '') {
            $oXml = simplexml_load_file($sUrl . $zip);
            if ($oXml !== false && isset($oXml->result) && isset($oXml->result->address_component)) {
                foreach ($oXml->result->address_component as $value) {
                    if ($value->type == 'route') {
                        $aAddress[1] = (string) $value->long_name;
                    }
                    if (isset($value->type[0])) {
                        if ($value->type[0] == 'postal_town')                 { $loc_level_1 = (string) $value->long_name; }
                        if ($value->type[0] == 'locality')                    { $loc_level_1 = (string) $value->long_name; }
                        if ($value->type[0] == 'administrative_area_level_1') { $loc_level_2 = (string) $value->long_name; }
                        if ($value->type[0] == 'administrative_area_level_2') { $loc_level_3 = (string) $value->long_name; }
                        if ($value->type[0] == 'country')                     { $loc_level_4 = (string) $value->long_name; }
                    }
                }
            } else {
                $aAddress[0] = 1;
            }
        } else {
            $aAddress[0] = 2;
        }

        $aAddress[2] = $loc_level_1;
        if ($loc_level_4 == 'United States') {
            $aAddress[3] = $loc_level_3;
        } else {
            $aAddress[3] = $loc_level_2;
        }

        return $aAddress;
    }

    public function getDistanceFromPointQuery($zipcode, $zipcodeColumn)
    {
        // Legacy method - distance calculation via Google Maps API
        // API key required for production use
        return array();
    }
}
