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
        $aAddress = array(0, '', '', '');

        if ($zip == '') {
            $aAddress[0] = 2;
            return $aAddress;
        }

        $sUrl = 'https://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=';
        $oXml = simplexml_load_file($sUrl . rawurlencode($zip));

        if ($oXml === false || !isset($oXml->result->address_component)) {
            $aAddress[0] = 1;
            return $aAddress;
        }

        $levels = $this->parseAddressComponents($oXml->result->address_component, $aAddress);
        $aAddress[2] = $levels['loc_level_1'];
        $aAddress[3] = ($levels['loc_level_4'] == 'United States') ? $levels['loc_level_3'] : $levels['loc_level_2'];

        return $aAddress;
    }

    private function parseAddressComponents($components, &$aAddress)
    {
        $levels = array('loc_level_1' => '', 'loc_level_2' => '', 'loc_level_3' => '', 'loc_level_4' => '');
        $typeMap = array(
            'postal_town'                => 'loc_level_1',
            'locality'                   => 'loc_level_1',
            'administrative_area_level_1'=> 'loc_level_2',
            'administrative_area_level_2'=> 'loc_level_3',
            'country'                    => 'loc_level_4',
        );

        foreach ($components as $value) {
            if ($value->type == 'route') {
                $aAddress[1] = (string) $value->long_name;
            }
            if (isset($value->type[0]) && isset($typeMap[(string)$value->type[0]])) {
                $levels[$typeMap[(string)$value->type[0]]] = (string) $value->long_name;
            }
        }

        return $levels;
    }

    public function getDistanceFromPointQuery($zipcode, $zipcodeColumn)
    {
        // Legacy wrapper - returns expected select/join keys for distance filtering
        $select = "(3958*3.1415926*sqrt((zipcode_searching.lat-zipcode_record.lat)*(zipcode_searching.lat-zipcode_record.lat) + cos(zipcode_searching.lat/57.29578)*cos(zipcode_record.lat/57.29578)*(zipcode_searching.lng-zipcode_record.lng)*(zipcode_searching.lng-zipcode_record.lng))/180) as distance_km";
        $join = "LEFT JOIN zipcodes as zipcode_searching ON zipcode_searching.zipcode = ".$zipcode." LEFT JOIN zipcodes as zipcode_record ON zipcode_record.zipcode = ".$zipcodeColumn;
        return array("select" => $select, "join" => $join);
    }
}
