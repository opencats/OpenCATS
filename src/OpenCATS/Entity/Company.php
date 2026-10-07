<?php
namespace OpenCATS\Entity;

class Company
{
    private $name;
    private $address;
    private $address2;
    private $city;
    private $state;
    private $zipCode;
    private $country;
    private $phoneNumberOne;
    private $phoneNumberTwo;
    private $faxNumber;
    private $url;
    private $keyTechnologies;
    private $isHot;
    private $notes;
    private $enteredBy;
    private $owner;
    private $commercialTier = null;
    private $relationshipStatus = null;
    
    function __construct($name)
    {
        $this->name = $name;
    }

    function getName()
    {
        return $this->name;
    }
    
    function setAddress($value)
    {
        $this->address = $value;
    }
    
    function getAddress()
    {
        return $this->address;
    }

    function setAddress2($value)
    {
        $this->address2 = $value;
    }

    function getAddress2()
    {
        return $this->address2;
    }
    
    function setCity($value)
    {
        $this->city = $value;
    }
    
    function getCity()
    {
        return $this->city;
    }
    
    function setState($value)
    {
        $this->state = $value;
    }
    
    function getState()
    {
        return $this->state;
    }
    
    function setZipCode($value)
    {
        $this->zipCode = $value;
    }
    
    function getZipCode()
    {
        return $this->zipCode;
    }

    function setCountry($value)
    {
        $this->country = $value;
    }

    function getCountry()
    {
        return $this->country;
    }
    
    function setPhoneNumberOne($value)
    {
        $this->phoneNumberOne = $value;
    }
    
    function getPhoneNumberOne()
    {
        return $this->phoneNumberOne;
    }
    
    function setPhoneNumberTwo($value)
    {
        $this->phoneNumberTwo = $value;
    }
    
    function getPhoneNumberTwo()
    {
        return $this->phoneNumberTwo;
    }
    
    function setFaxNumber($value)
    {
        $this->faxNumber = $value;
    }
    
    function getFaxNumber()
    {
        return $this->faxNumber;
    }

    // TODO: URL should be renamed to Website as URL is a technical but a business concept
    function setUrl($value)
    {
        $this->url = $value;
    }
    
    function getUrl()
    {
        return $this->url;
    }
    
    function setKeyTechnologies($value)
    {
        $this->keyTechnologies = $value;
    }
    
    function getKeyTechnologies()
    {
        return $this->keyTechnologies;
    }
    
    function setIsHot($value)
    {
        $this->isHot = $value;
    }
    
    function isHot()
    {
        return $this->isHot;
    }
    
    function setNotes($value)
    {
        $this->notes = $value;
    }
    
    function getNotes()
    {
        return $this->notes;
    }
    
    // TODO: Rename EnteredBy to EnteredByUser, to make it explicit that's
    // awaiting for a user id
    function setEnteredBy($value)
    {
        $this->enteredBy = $value;
    }
    
    function getEnteredBy()
    {
        return $this->enteredBy;
    }
    
    // TODO: Make explicit that the owner is a user
    function setOwner($value)
    {
        $this->owner = $value;
    }
    
    function getOwner()
    {
        return $this->owner;
    }
    
    public static function getCommercialTiers()
    {
        return array('A', 'B', 'C', 'D');
    }

    public static function getRelationshipStatuses()
    {
        return array('Fresh Prospect', 'Prospect', 'Engaged', 'Client', 'Dormant', 'Lost');
    }

    public static function normalizeClassification($value, $allowed)
    {
        if ($value === null || $value === '')
        {
            return null;
        }
        if (!is_string($value) || !in_array($value, $allowed, true))
        {
            throw new \InvalidArgumentException('Invalid Company classification.');
        }
        return $value;
    }

    public function setCommercialTier($value)
    {
        $this->commercialTier = self::normalizeClassification($value, self::getCommercialTiers());
    }

    public function getCommercialTier()
    {
        return $this->commercialTier;
    }

    public function setRelationshipStatus($value)
    {
        $this->relationshipStatus = self::normalizeClassification($value, self::getRelationshipStatuses());
    }

    public function getRelationshipStatus()
    {
        return $this->relationshipStatus;
    }

    static function create(
        $name,
        $address,
        $address2,
        $city,
        $state,
        $zipCode,
        $country,
        $phoneNumberOne,
        $phoneNumberTwo,
        $faxNumber,
        $url,
        $keyTechnologies,
        $isHot,
        $notes,
        $enteredBy,
        $owner,
        $commercialTier = null,
        $relationshipStatus = null
    )
    {
        $company = new Company($name);
        $company->setAddress($address);
        $company->setAddress2($address2);
        $company->setCity($city);
        $company->setState($state);
        $company->setZipCode($zipCode);
        $company->setCountry($country);
        $company->setPhoneNumberOne($phoneNumberOne);
        $company->setPhoneNumberTwo($phoneNumberTwo);
        $company->setFaxNumber($faxNumber);
        $company->setUrl($url);
        $company->setKeyTechnologies($keyTechnologies);
        $company->setIsHot($isHot);
        $company->setNotes($notes);
        $company->setEnteredBy($enteredBy);
        $company->setOwner($owner);
        $company->setCommercialTier($commercialTier);
        $company->setRelationshipStatus($relationshipStatus);
        return $company;
    }
}
