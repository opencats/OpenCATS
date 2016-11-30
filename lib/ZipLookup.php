<?php
/**
* Google API Zip Code Lookup library
*/
class ZipLookup
{

     public static function makeSearchableUSZip($zipString)
     {

	return str_replace(' ', '', $zipString);
     }

    public function getCityStateByZip($zip)
    {


	$aAddress[0] = 0;
	$aAddress[1] = '';
	$aAddress[2] = '';
	$aAddress[3] = '';

	$sUrl = 'http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=';

	if ($zip != '') {
		if (($oXml = simplexml_load_file($sUrl . $zip))) {
			foreach($oXml->result->address_component as $value) {
				if ($value->type == 'route') {
					$aAddress[1] = (string) $value->long_name;
				}
				if ($value->type[0] == 'locality') {
					$aAddress[2] = (string) $value->long_name;
				}
				if (($value->type == 'postal_town') && ($aAddress[2] == '')) {
					$aAddress[2] = (string) $value->long_name;
				}
				if ($value->type[0] == 'administrative_area_level_2') {
					$aAddress[3] = (string) $value->long_name;
				}
			}
		} else {
			$aAddress[0] = 1;
		}
	} else {
		$aAddress[0] = 2;
	}

	return $aAddress;

    }
    
    /**
     * Returns an array of SQL clauses that returns the distance from a zipcode for each record.
     *
     * @param integer United States Zip code (55303)
     * @param string record Zip Code Column (candidate.zip)
     * @return string SQL select clause
     */
    public function getDistanceFromPointQuery($zipcode, $zipcodeColumn)
    {
        //based on kilometers = (3958*3.1415926*sqrt(($lat2-$lat1)*($lat2-$lat1) + cos($lat2/57.29578)*cos($lat1/57.29578)*($lon2-$lon1)*($lon2-$lon1))/180);
        
        $select = "(3958*3.1415926*sqrt((zipcode_searching.lat-zipcode_record.lat)*(zipcode_searching.lat-zipcode_record.lat) + cos(zipcode_searching.lat/57.29578)*cos(zipcode_record.lat/57.29578)*(zipcode_searching.lng-zipcode_record.lng)*(zipcode_searching.lng-zipcode_record.lng))/180) as distance_km";
        $join = "LEFT JOIN zipcodes as zipcode_searching ON zipcode_searching.zipcode = ".$zipcode." LEFT JOIN zipcodes as zipcode_record ON zipcode_record.zipcode = ".$zipcodeColumn;
        return array("select" => $select, "join" => $join);
    }
}
?>
