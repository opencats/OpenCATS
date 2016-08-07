<?php
namespace OpenCATS\Entity;

class Company
{
    private $siteId;
    private $name;
    private $address;
    private $city;
    private $state;
    private $zipCode;
    private $phoneNumberOne;
    private $phoneNumberTwo;
    private $faxNumber;
    private $url;
    private $keyTechnologies;
    private $isHot;
    private $notes;
    private $enteredBy;
    private $owner;
    
    function __construct($siteId, $name)
    {
        $this->siteId = $siteId;
        $this->name = $name;
    }
    
    function getSiteId()
    {
        return $this->siteId;
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
    
    static function create(
        $siteId,
        $name,
        $address,
        $city,
        $state,
        $zipCode,
        $phoneNumberOne,
        $phoneNumberTwo,
        $faxNumber,
        $url,
        $keyTechnologies,
        $isHot,
        $notes,
        $enteredBy,
        $owner
    )
    {
        $company = new Company($siteId, $name);
        $company->setAddress($address);
        $company->setCity($city);
        $company->setState($state);
        $company->setZipCode($zipCode);
        $company->setPhoneNumberOne($phoneNumberOne);
        $company->setPhoneNumberTwo($phoneNumberTwo);
        $company->setFaxNumber($faxNumber);
        $company->setUrl($url);
        $company->setKeyTechnologies($keyTechnologies);
        $company->setIsHot($isHot);
        $company->setNotes($notes);
        $company->setEnteredBy($enteredBy);
        $company->setOwner($owner);
        return $company;
    }
}
