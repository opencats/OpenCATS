<?php
/*
   * OSATS
   * GNU License
   *
   *
   * @package    OSATS
   * @subpackage Library
   * @copyright Open Source
   * @version    1.0
*/

/**
 *	Standard AJAX Interface
 *	@package    OSATS
 *	@subpackage Library
 */
class AJAXInterface
{
    /**
     * Echos an XML document back to the company. The XML string should not
     * contain the <?xml tag.
     *
     * @param string Output XML.
     * @return void
     */
    public function outputXMLPage($xmlString)
    {
        header('Content-type: text/xml');

        echo '<?xml version="1.0" encoding="', AJAX_ENCODING, '"?>', "\n";
        echo $xmlString;
    }

    /**
     * Echos an XML error document back to the company.
     *
     * @param string Error output XML.
     * @return void
     */
    public function outputXMLErrorPage($errorCode, $errorMessage)
    {
        $this->outputXMLPage(
            "<data>\n" .
            "    <errorcode>"    . $errorCode . "</errorcode>\n" .
            "    <errormessage>" . $errorMessage . "</errormessage>\n" .
            "</data>\n"
        );
    }

    /**
     * Echos an XML success document back to the company.
     *
     * @param string XML response message.
     * @return void
     */
    public function outputXMLSuccessPage($response = 'Success!')
    {
        $this->outputXMLPage(
            "<data>\n" .
            "    <errorcode>0</errorcode>\n" .
            "    <errormessage></errormessage>\n" .
            "    <response>" . $response . "</response>\n" .
            "</data>\n"
        );
    }

    /**
     * Returns true if a required numeric ID ($key) is a) present in $_POST,
     * b) not empty, and c) a digit / whole number.
     *
     * @param string Request key name of ID.
     * @param string Allow ID to be 0?
     * @param string Allow ID to be negative?
     * @return boolean Does the value pass all tests?
     */
    public function isRequiredIDValid($key, $allowZero = true,
        $allowNegative = false)
    {
        /* Return false if the key is not present. */
        if (!isset($_REQUEST[$key]))
        {
            return false;
        }

        $value = (string) $_REQUEST[$key];

        /* Return false if the key is empty, or if the key is zero and
         * zero-values are not allowed.
         */
        if (empty($value) && ($value !== '0' || !$allowZero))
        {
            return false;
        }

        /* -0 should not be allowed. */
        if ($value === '-0')
        {
            return false;
        }

        /* If allowing negatives, strip the first character if it's a '-'. */
        if ($allowNegative && $value[0] === '-')
        {
            $value = substr($value, 1);
        }

        /* Only allow digits. */
        if (!ctype_digit($value))
        {
            return false;
        }

        return true;
    }

    /**
     * Returns true if an optional numeric ID ($key) is a) present in $_POST,
     * b) not empty, and c) either 'NULL' / a digit / whole number.
     *
     * @param string Request key name of ID.
     * @return boolean Does the value pass all tests?
     */
    public function isOptionalIDValid($key)
    {
        if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key]) &&
            ($_REQUEST[$key] == 'NULL' ||
            ctype_digit((string) $_REQUEST[$key])))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if a checkbox by the name of $key is checked in $request.
     *
     * @param string Request variable name / key.
     * @param array $_GET, $_POST, or $_REQUEST
     * @return boolean Is checkbox checked?
     */
    public function isChecked($key)
    {
        if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key]) &&
            $_REQUEST[$key] != 'false' && $_REQUEST[$key] != 'off')
        {
            return true;
        }

        return false;
    }

    /**
     * Returns trim()'d form input if $key is in $request; otherwise ''.
     *
     * @param string Request variable name / key.
     * @return string Trimmed value or ''.
     */
    public function getTrimmedInput($key)
    {
        if (isset($_REQUEST[$key]))
        {
            return trim($_REQUEST[$key]);
        }

        return '';
    }
}

/**
 *	Secure AJAX Interface
 *	@package    OSATS
 *	@subpackage Library
 */
class SecureAJAXInterface extends AJAXInterface
{
    private $_siteID;
    private $_userID;


    public function __construct()
    {
        /* Give the session a unique name to avoid conflicts and start the
         * session. */
        @session_name(SESSION_NAME);
        session_start();

        /* Validate the session. */
        if (!$this->isSessionLoggedIn())
        {
            $this->outputXMLErrorPage(
                -1, 'You are not logged in. Please log out and log back in.'
            );
            die();
        }

        /* Grab the current user's site ID. */
        $this->_siteID = $_SESSION['OSATS']->getSiteID();

        /* Grab the current user's user ID. */
        $this->_userID = $_SESSION['OSATS']->getUserID();
    }


    /**
     * Returns the current session's site ID.
     *
     * @return integer Site ID.
     */
    public function getSiteID()
    {
        return $this->_siteID;
    }

    /**
     * Returns the current session's user ID.
     *
     * @return integer User ID.
     */
    public function getUserID()
    {
        return $this->_userID;
    }

    /**
     * Checks to see if we have a valid, logged in session.
     *
     * @return boolean Is the current session valid and logged in?
     */
    public function isSessionLoggedIn()
    {
        if (!isset($_SESSION['OSATS']) || empty($_SESSION['OSATS']) ||
            !$_SESSION['OSATS']->isLoggedIn())
        {
            return false;
        }

        return true;
    }
}