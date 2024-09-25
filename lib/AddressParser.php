<?php

/* Flags for default phone number. */
define('ADDRESSPARSER_MODE_PERSON', 1);
define('ADDRESSPARSER_MODE_CONTACT', 2);
define('ADDRESSPARSER_MODE_COMPANY', 3);

class AddressParser
{
    protected $_firstName;

    protected $_middleName;

    protected $_lastName;

    protected $_company;

    protected $_addressLineOne;

    protected $_addressLineTwo;

    protected $_city;

    protected $_state;

    protected $_zip;

    protected $_email;

    protected $_phoneNumbers;

    protected $_addressBlock;

    protected $_mode;

    public function parse($addressBlockString, $mode)
    {
        $this->_initialize($addressBlockString, $mode);

        // Extract email
        $this->_email = $this->_extractEmailAddress();

        // Extract phone number
        $this->_phoneNumbers = $this->_extractPhoneNumber();

        // Extract address
        $this->_extractAddress();
    }

    public function getAddressArray()
    {
        return [
            'company' => $this->_company ?? '',  // Ensure 'company' is always set
            'firstName' => $this->_firstName,
            'middleName' => $this->_middleName,
            'lastName' => $this->_lastName,
            'addressLineOne' => $this->_addressLineOne,
            'addressLineTwo' => $this->_addressLineTwo,
            'city' => $this->_city,
            'state' => $this->_state,
            'zip' => $this->_zip,
            'email' => $this->_email,
            'phoneNumbers' => $this->_phoneNumbers,
        ];
    }

    protected function _initialize($addressBlock, $mode)
    {
        $this->_firstName = '';
        $this->_middleName = '';
        $this->_lastName = '';
        $this->_addressLineOne = '';
        $this->_addressLineTwo = '';
        $this->_city = '';
        $this->_state = '';
        $this->_zip = '';
        $this->_email = '';
        $this->_phoneNumbers = [];

        // Split the input by new lines
        $this->_addressBlock = explode("\n", trim($addressBlock));
        $this->_mode = $mode;
    }

    protected function _extractEmailAddress()
    {
        foreach ($this->_addressBlock as $lineNumber => $line) {
            if (stripos($line, 'Email') !== false) {
                // Extract email
                if (preg_match('/mailto:([^\s]+)/', $line, $matches)) {
                    return $matches[1];
                }
            }
        }
        return '';
    }

    protected function _extractPhoneNumber()
    {
        foreach ($this->_addressBlock as $line) {
            if (stripos($line, 'Phone') !== false) {
                // Extract phone number
                if (preg_match('/Phone\.\s*(\d{10,})/', $line, $matches)) {
                    return [
                        [
                            'number' => $matches[1],
                            'type' => 'general',
                        ],
                    ];
                }
            }
        }
        return [];
    }

    protected function _extractAddress()
    {
        foreach ($this->_addressBlock as $line) {
            if (stripos($line, 'Address') !== false) {
                // Extract address line, assuming address follows "Address."
                if (preg_match('/Address\.\s*(.+),\s*([A-Za-z\s]+),\s*([A-Za-z\s]+),\s*(\w+-\w+)/', $line, $matches)) {
                    $this->_addressLineOne = $matches[1];
                    $this->_city = $matches[2];
                    $this->_state = $matches[3];
                    $this->_zip = $matches[4];
                }
            }
        }
    }
}
