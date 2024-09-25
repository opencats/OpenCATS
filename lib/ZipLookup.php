<?php

include_once('./vendor/autoload.php'); // Google API Client Library Autoloader

class ZipLookup
{
    private $apiKey;

    // Constructor now uses the API key from config.php
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Make the US Zip code searchable by removing spaces
     */
    public static function makeSearchableUSZip($zipString)
    {
        return str_replace(' ', '', $zipString); // Remove spaces from ZIP code
    }

    /**
     * Get City and State by Zip Code using Google Geocoding API
     */
    public function getCityStateByZip($zip)
    {
        $client = new \GuzzleHttp\Client();

        // Log the request for debugging
        // file_put_contents('/var/www/html/opencats-sync/OpenCATS/logfile.txt', "Requesting zip: $zip\n", FILE_APPEND);

        // Make the request to Google Geocoding API
        try {
            $response = $client->request('GET', 'https://maps.googleapis.com/maps/api/geocode/json', [
                'query' => [
                    'address' => $zip,
                    'key' => $this->apiKey,
                ],
            ]);
        } catch (Exception $e) {
            // Log the exception
            // file_put_contents('/var/www/html/opencats-sync/OpenCATS/logfile.txt', "Error in API request: " . $e->getMessage() . "\n", FILE_APPEND);
            return null; // Return null if API call fails
        }

        $data = json_decode($response->getBody(), true);

        // Log the response for debugging
        // file_put_contents('/var/www/html/opencats-sync/OpenCATS/logfile.txt', print_r($data, true), FILE_APPEND);

        // Check if valid data is received
        if (isset($data['results'][0])) {
            $addressComponents = $data['results'][0]['address_components'];
            $city = '';
            $state = '';
            $country = '';

            // Extract city, state, and country from address components
            foreach ($addressComponents as $component) {
                if (in_array('locality', $component['types'])) {
                    $city = $component['long_name'];
                }
                if (in_array('administrative_area_level_1', $component['types'])) {
                    $state = $component['long_name'];
                }
                if (in_array('country', $component['types'])) {
                    $country = $component['long_name'];
                }
            }

            // Log the extracted city and state
            // file_put_contents('/var/www/html/opencats-sync/OpenCATS/logfile.txt', "City: $city, State: $state, Country: $country\n", FILE_APPEND);

            // Return the location details
            return [
                'city' => $city,
                'state' => $state,
                'country' => $country,
            ];
        }

        // Log the case where no valid results were found
        // file_put_contents('/var/www/html/opencats-sync/OpenCATS/logfile.txt', "No valid results found for zip: $zip\n", FILE_APPEND);

        // Return null if no results are found
        return null;
    }
}
