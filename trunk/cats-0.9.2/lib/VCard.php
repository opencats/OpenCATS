<?php
/**
 * CATS
 * vCard Generation Library
 *
 * Portions Copyright (C) 2006 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * Based on PHP vCard class v2.0 by Kai Blankenhorn.
 *
 * Notes on vCards:
 *   * Defined by RFC 2426 (http://www.faqs.org/rfcs/rfc2426.html).
 *   * The vCard 2.1 specification (as this library conforms to) is available
 *     at http://www.imc.org/pdi/vcard-21.rtf.
 *   * A vCard MUST contain a Name and Formatted Name according to the
 *     specifications and the implementation in this application.
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: VCard.php 3587 2007-11-13 03:55:57Z will $
 */

/**
 *	vCard Library
 *	@package    CATS
 *	@subpackage Library
 */
class VCard
{
    private $_properties = array();
    private $_filename;

    /* vCard specification version. */
    const VCARD_VERSION = '2.1';


    /**
     * Adds a name to a vCard (REQUIRED!). Multiple additional names are
     * comma-separated (for example, for Laura Martha Long Smith, 'Martha,Long'
     * would be used). If no formatted name is specified, 'Prefix First
     * Additional Last Suffix' will be used.
     *
     * @param string Last name.
     * @param string First name.
     * @param string Middle name(s) / additional name(s).
     * @param string Name prefix ('Dr.', etc.).
     * @param string Name suffix ('Jr.', etc.).
     * @param string Formatted name ('Will G. Buckner', etc.).
     * @return void
     */
    public function setName($lastName, $firstName, $additionalNames = '',
                            $prefix = '', $suffix = '', $formattedName = '')
    {
        /* From the vCard 2.1 specification:
         *   The property value is a concatenation of the Family Name (first
         *   field), Given Name (second field), Additional Names (third field),
         *   Name Prefix (fourth field), and Name Suffix (fifth field) strings.
         *
         *   The following is an example of the Name property for a person:
         *
         *     N:Public;John;Quinlan;Mr.;Esq.
         *
         *   The following is an example of the Name property for a resource or place:
         *
         *     N:Veni, Vidi, Vici;The Restaurant.
         */
        $name = sprintf(
            '%s;%s;%s;%s;%s',
            $this->_encode($lastName),
            $this->_encode($firstName),
            $this->_encode($additionalNames),
            $this->_encode($prefix),
            $this->_encode($suffix)
        );

        $this->_properties[] = array(
            'N',
            $name
        );

        /* Filename is 'First Last.vcf'. */
        $this->_filename = sprintf('%s %s.vcf', $firstName, $lastName);

        /* Create a formatted name if none was specified. */
        if ($formattedName == '')
        {
            $formattedName = trim(
                sprintf(
                    '%s %s %s %s %s',
                    $prefix,
                    $firstName,
                    $additionalNames,
                    $lastName,
                    $suffix
                )
            );
            $formattedName = str_replace('  ', ' ', $formattedName);
        }

        $this->_properties[] = array(
            'FN',
            $this->_encode($formattedName)
        );
    }

    /**
     * Adds a phone number to the vCard. Valid values for $type are 'PREF',
     * 'WORK', 'HOME', 'VOICE', 'FAX', 'MSG', 'CELL', 'PAGER', 'BBS', 'MODEM',
     * 'CAR', 'ISDN', 'VIDEO' or any sensible combination (for example,
     * 'PREF;WORK;VOICE'). The default type is 'VOICE'. Multiple phone numbers
     * can be specified in one vCard.
     *
     * @param string Phone number.
     * @param string Phone number type.
     * @return void
     */
    public function setPhoneNumber($phoneNumber, $type = 'VOICE')
    {
        $this->_properties[] = array(
            'TEL;' . $type,
            $phoneNumber
        );
    }

    /**
     * Adds an e-mail address to the vCard.
     *
     * @param string E-mail address.
     * @return void
     */
    public function setEmail($emailAddress)
    {
        $this->_properties[] = array(
            'EMAIL;INTERNET',
            $emailAddress
        );
    }

    /**
     * Adds a contact title to the vCard.
     *
     * @param string Title.
     * @return void
     */
    public function setTitle($title)
    {
        $this->_properties[] = array(
            'TITLE;ENCODING=QUOTED-PRINTABLE',
            $this->_encode($title)
        );
    }

    /**
     * Adds a organization name to the vCard.
     *
     * @param string Organization name.
     * @return void
     */
    public function setOrganization($organization)
    {
        $this->_properties[] = array(
            'ORG;ENCODING=QUOTED-PRINTABLE',
            $this->_encode($organization)
        );
    }

    /**
     * Adds an address to the vCard. Valid values for $type are 'DOM', 'INTL',
     * 'WORK', 'HOME', 'POSTAL', 'PARCEL', or any sensible combination (for
     * example, 'DOM;WORK;POSTAL;PARCEL'). The default type is '' (no type),
     * as a type is optional in the vCard specification.
     *
     * @param string Street address (e.g. 123 Mine Dr.).
     * @param string Extended address (e.g. Ste 101).
     * @param string City.
     * @param string State / province / region.
     * @param string Postal / zip code.
     * @param string X.500 post office address (e.g. P.O. Box 10101).
     * @param string Country.
     * @param string Address type.
     * @return void
     */
    public function setAddress($streetAddress, $extendedAddress, $city,
        $region, $postalCode, $postOfficeAddress = '', $country = '',
        $label = '', $type = '')
    {
        if ($type != '')
        {
            $property = 'ADR;' . $type . ';ENCODING=QUOTED-PRINTABLE';
        }
        else
        {
            $property = 'ADR;ENCODING=QUOTED-PRINTABLE';
        }

        /* From the vCard 2.1 specification:
         *   The property value is a concatenation of the Post Office Address
         *   (first field) Extended Address (second field), Street (third
         *   field), Locality (fourth field), Region (fifth field), Postal Code
         *   (six field), and Country (seventh field) strings. An example of
         *   this property follows:
         *
         *      ADR;DOM;HOME:P.O. Box 101;Suite 101;123 \ (cont'd, one line)
         *      Main Street;Any Town;CA;91921-1234;
         */
        $address = trim(
            sprintf(
                '%s;%s;%s;%s;%s;%s;%s',
                $this->_encode($postOfficeAddress),
                $this->_encode($extendedAddress),
                $this->_encode($streetAddress),
                $this->_encode($city),
                $this->_encode($region),
                $this->_encode($postalCode),
                $this->_encode($country)
            )
        );

        $this->_properties[] = array(
            $property,
            $address
        );
    }

    /**
     * Adds a note to the vCard.
     *
     * @param string Note.
     * @return void
     */
    public function setNote($note)
    {
        $this->_properties[] = array(
            'ORG;ENCODING=QUOTED-PRINTABLE',
            $this->_encode($note)
        );
    }

    /**
     * Adds a URL to the vCard. Valid values for $type are 'WORK', 'HOME', ''.
     * The default type is '' (no type), as the vCard 2.1 specification does
     * not appear to support URL types.
     *
     * @param string URL.
     * @param string URL type.
     * @return void
     */
    public function setURL($url, $type = '')
    {
        if ($type != '')
        {
            $property = 'URL;' . $type;
        }
        else
        {
            $property = 'URL';
        }

        $this->_properties[] = array(
            $property,
            $url
        );
    }

    /**
     * Adds a photo to the vCard (gets encoded in base64). Valid values for
     * $type are 'GIF', 'CGM', 'WMF', 'BMP', 'MET', 'PMB', 'DIB', 'PICT',
     * 'TIFF', 'PS', 'PDF', 'JPEG'. The default type is 'JPEG'.
     *
     * @param string Binary photo data.
     * @param string Photo type.
     * @return void
     */
    public function setPhoto($photo, $type = 'JPEG')
    {
        $this->_properties[] = array(
            'PHOTO;TYPE=' . $type . ';ENCODING=BASE64',
            base64_encode($photo)
        );
    }

    /**
     * Adds a birth date to the vCard. The date format is YYYY-MM-DD or YYMMDD
     * (ISO 8601 basic or extended formats).
     *
     * @param string Bitchday date.
     * @return void
     */
    public function setBirthday($date)
    {
        $this->_properties[] = array(
            'BDAY',
            $date
        );
    }

    /**
     * Returns the vCard data.
     *
     * @return string vCard data.
     */
    public function getVCard()
    {
        $vCard = sprintf("BEGIN:VCARD\r\nVERSION:%s\r\n", self::VCARD_VERSION);

        /* Add all other properties (set via setter methods) to the vCard. */
        foreach ($this->_properties as $key => $value)
        {
            $vCard .= sprintf("%s:%s\r\n", $value[0], $value[1]);
        }

        /* Revision date is in ISO 8601 basic format.
         *
         * From the vCard specification:
         *
         *   The following example is in the basic format and local time of
         *   ISO 8601:
         *     REV:19951031T222710
         *
         *  The following example is in the extended format and UTC time of
         *  ISO 8601:
         *      REV:1995-10-31T22:27:10Z
         */
        $vCard .= sprintf(
            "REV:%s\r\nMAILER:CATS\r\nEND:VCARD\r\n",
            date('Ymd\THis')
        );

        return $vCard;
    }

    /**
     * Returns the vCard filename.
     *
     * @return string vCard filename.
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * Prints the vCard and all attachment Content / Connection headers.
     * Headers to disable caching are already sent by index.php.
     *
     * @return void
     */
    public function printVCardWithHeaders()
    {
        $output = $this->getVCard();

        header('Content-Disposition: attachment; filename="' . $this->_filename . '"');
        header('Content-Length: ' . strlen($output));
        header('Connection: close');
        header('Content-Type: text/x-vCard; name=' . $this->_filename);

        echo $output;
    }


    // FIXME: Document me.
    private function _encode($string)
    {
        return str_replace(
            ';', '\;', StringUtility::quotedPrintableEncode($string)
        );
    }
}

?>
