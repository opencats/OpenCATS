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

    public function __construct($siteId, $name)
    {
        $this->siteId = $siteId;
        $this->name = $name;
    }

    public function getSiteId()
    {
        return $this->siteId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setAddress($value)
    {
        $this->address = $value;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setCity($value)
    {
        $this->city = $value;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setState($value)
    {
        $this->state = $value;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setZipCode($value)
    {
        $this->zipCode = $value;
    }

    public function getZipCode()
    {
        return $this->zipCode;
    }

    public function setPhoneNumberOne($value)
    {
        $this->phoneNumberOne = $value;
    }

    public function getPhoneNumberOne()
    {
        return $this->phoneNumberOne;
    }

    public function setPhoneNumberTwo($value)
    {
        $this->phoneNumberTwo = $value;
    }

    public function getPhoneNumberTwo()
    {
        return $this->phoneNumberTwo;
    }

    public function setFaxNumber($value)
    {
        $this->faxNumber = $value;
    }

    public function getFaxNumber()
    {
        return $this->faxNumber;
    }

    // TODO: URL should be renamed to Website as URL is a technical but a business concept
    public function setUrl($value)
    {
        $this->url = $value;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setKeyTechnologies($value)
    {
        $this->keyTechnologies = $value;
    }

    public function getKeyTechnologies()
    {
        return $this->keyTechnologies;
    }

    public function setIsHot($value)
    {
        $this->isHot = $value;
    }

    public function isHot()
    {
        return $this->isHot;
    }

    public function setNotes($value)
    {
        $this->notes = $value;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    // TODO: Rename EnteredBy to EnteredByUser, to make it explicit that's
    // awaiting for a user id
    public function setEnteredBy($value)
    {
        $this->enteredBy = $value;
    }

    public function getEnteredBy()
    {
        return $this->enteredBy;
    }

    // TODO: Make explicit that the owner is a user
    public function setOwner($value)
    {
        $this->owner = $value;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public static function create(
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
    ) {
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
