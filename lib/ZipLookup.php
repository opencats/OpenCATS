<?php
/**
* OpenStreetMap (Nominatim) Zip Code Lookup library
*/
class ZipLookup
{
     public static function makeSearchableUSZip($zipString)
     {

	return str_replace(' ', '', $zipString);
     }

    public function getCityStateByZip($zip, $country = '')
    {


	$aAddress[0] = 0;
	$aAddress[1] = '';
	$aAddress[2] = '';
	$aAddress[3] = '';

	if ($zip == '') {
		$aAddress[0] = 2;
		return $aAddress;
	}

	$sUrl = 'https://nominatim.openstreetmap.org/search?format=jsonv2'
	      . '&addressdetails=1&limit=1&postalcode='
	      . rawurlencode($zip);

	/* Postal codes are not globally unique, so without a country the first
	 * match can land in the wrong one. Scope by the ISO country code when the
	 * form supplies it. */
	$sCountry = strtolower(preg_replace('/[^A-Za-z]/', '', $country));
	if (strlen($sCountry) == 2) {
		$sUrl .= '&countrycodes=' . $sCountry;
	}

	/* Nominatim's usage policy requires a User-Agent that identifies the
	 * calling application; use the site's host so each install is distinct. */
	$sHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'unknown-host';
	$context = stream_context_create(array(
		'http' => array(
			'method'  => 'GET',
			'header'  => 'User-Agent: OpenCATS-ZipLookup (' . $sHost . ")\r\n",
			'timeout' => 10,
		),
	));

	$sResponse = @file_get_contents($sUrl, false, $context);
	if ($sResponse === false) {
		$aAddress[0] = 1;
		return $aAddress;
	}

	$aResults = json_decode($sResponse, true);
	if (!is_array($aResults) || empty($aResults) || empty($aResults[0]['address'])) {
		$aAddress[0] = 1;
		return $aAddress;
	}

	$aParts = $aResults[0]['address'];

	if (isset($aParts['road'])) {
		$aAddress[1] = (string) $aParts['road'];
	}

	// Nominatim spreads the locality across several keys depending on place size.
	foreach (array('city', 'town', 'village', 'hamlet', 'municipality') as $sKey) {
		if (isset($aParts[$sKey])) {
			$aAddress[2] = (string) $aParts[$sKey];
			break;
		}
	}

	if (isset($aParts['state'])) {
		$aAddress[3] = (string) $aParts['state'];
	} else if (isset($aParts['county'])) {
		$aAddress[3] = (string) $aParts['county'];
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
