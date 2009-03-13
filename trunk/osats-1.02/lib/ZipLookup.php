 <?php
/**
 * OSATS
 */

/**
 *	Zip Code Lookup Library
 *	@package    OSATS
 *	@subpackage Library
 */
class ZipLookup
{
    /**
     * Makes a proper "searchable" United States Zip code from a given Zip
     * code string. All whitespace and leading 0's are removed.
     *
     * @param string free-form Zip code (ex: 55121, 30504 - 1000, 01369-5463)
     * @return integer 3-5 digit searchable zip code or 0 on failure
     */
     public static function makeSearchableUSZip($zipString)
     {
        if (preg_match('/^\s*[0]*(\d{3,5})\s*(?:-.*)?$/', $zipString, $match))
        {
            return (int) $match[1];
        }

        return 0;
     }

    /**
     * Finds City and State names via United States Zip code. The Zip code
     * should be specified as an integer with no leading 0s.
     *
     * @param integer United States Zip code
     * @return array city / state data (empty strings if not found)
     */
    public function getCityStateByZip($zip)
    {
        /* Make sure we have an integer. */
        $zip = (int) $zip;

        if ($zip === 0)
        {
            return array('city' => '', 'state' => '');
        }

        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                city AS city,
                state AS state
            FROM
                zipcodes
            WHERE
                zipcode = %s",
            $zip
        );
        $data = $db->getAssoc($sql);

        if (empty($data))
        {
            return array('city' => '', 'state' => '');
        }

        return $data;
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