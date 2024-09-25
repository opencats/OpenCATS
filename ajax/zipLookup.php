<?php

require_once(LEGACY_ROOT . '/lib/ZipLookup.php');
require_once(LEGACY_ROOT . '/config.php'); // Load the config to get the Google API key

// Start output buffering
ob_start();

header('Content-Type: text/xml');

// Log the incoming request for debugging purposes
file_put_contents('/var/www/html/opencats-sync/OpenCATS/logfile.txt', "Incoming request: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Ensure the ZIP code is provided using POST
if (! isset($_POST['zip']) || empty($_POST['zip'])) {
    file_put_contents('/var/www/html/opencats-sync/OpenCATS/logfile.txt', "No ZIP code provided\n", FILE_APPEND);
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<data><errorcode>1</errorcode><errormessage>No ZIP code provided</errormessage></data>";
    ob_end_flush(); // Flush output
    die();
}

// Proceed with processing the ZIP code
$zip = ZipLookup::makeSearchableUSZip($_POST['zip']);
file_put_contents('/var/www/html/opencats-sync/OpenCATS/logfile.txt', "ZIP received: $zip\n", FILE_APPEND);

// Instantiate ZipLookup with the API key from config.php
$zipLookup = new ZipLookup(GOOGLE_API_KEY);

// Call the ZipLookup class to retrieve location information
$location = $zipLookup->getCityStateByZip($zip);
file_put_contents('/var/www/html/opencats-sync/OpenCATS/logfile.txt', "Location data: " . print_r($location, true) . "\n", FILE_APPEND);

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<data>";

// Return the location data or an error message if the lookup failed
if ($location) {
    $address = '';  // Modify if you need address info
    $city = isset($location['city']) ? $location['city'] : '';
    $state = isset($location['state']) ? $location['state'] : '';

    echo "<errorcode>0</errorcode>";
    echo "<errormessage></errormessage>";
    echo "<address>{$address}</address>";
    echo "<city>{$city}</city>";
    echo "<state>{$state}</state>";
} else {
    echo "<errorcode>1</errorcode><errormessage>Location not found</errormessage>";
}

echo "</data>";

ob_end_flush(); // Flush output
