<?php
namespace OpenCATS\Entity;

class JobOrder
{
    private $id;
    private $title;
    private $companyId;
    private $contactId;
    private $description;
    private $notes;
    private $duration;
    private $maxRate;
    private $type;
    private $isHot;
    private $isPublic;
    private $openings;
    private $availableOpenings;
    private $companyJobId;
    private $salary;
    private $city;
    private $state;
    private $startDate;
    private $enteredBy;
    private $recruiter;
    private $owner;
    private $departmentId;
    private $questionnaire;
    private $siteId;
    
    function __construct(
        $siteId,
        $title,
        $type,
        $status,
        $city,
        $state,
        $enteredBy,
        $isPublic
    ) {
        $this->siteId = $siteId;
        $this->title = $title;
        $this->type = $type;
        $this->status = $status;
        $this->city = $city;
        $this->state = $state;
        $this->isPublic = $isPublic;
    }
    
    function getTitle()
    {
        return $this->title;
    }
    
    function getCompanyJobId()
    {
        return $this->companyJobId;
    }
    
    function setCompanyJobId($value)
    {
        $this->companyJobId = $value;
    }
    
    function getCompanyId()
    {
        return $this->companyId;
    }
    
    function setCompanyId($value)
    {
        $this->companyId = $value;
    }
    
    function getContactId()
    {
        return $this->contactId;
    }
    
    function setContactId($value)
    {
        $this->contactId = $value;
    }
    
    function getDescription()
    {
        return $this->description;
    }
    
    function setDescription($value)
    {
        $this->description = $value;
    }
    
    function getNotes()
    {
        return $this->notes;
    }
    
    function setNotes($value)
    {
        $this->notes = $value;
    }
    
    function getDuration()
    {
        return $this->duration;
    }
    
    function setDuration($value)
    {
        $this->duration = $value;
    }
    
    function getMaxRate()
    {
        return $this->maxRate;
    }
    
    function setMaxRate($value)
    {
        $this->maxRate = $value;
    }
    
    function getType()
    {
        return $this->type;
    }
    
    function setType($value)
    {
        $this->type = $value;
    }
    
    function isHot()
    {
        return $this->isHot;
    }
    
    function setIsHot($value)
    {
        $this->isHot = $value;
    }
    
    function isPublic()
    {
        return $this->isPublic;
    }
    
    function getOpenings()
    {
        return $this->openings;
    }
    
    function setOpenings($value)
    {
        $this->openings = $value;
    }
    
    function getAvailableOpenings()
    {
        return $this->availableOpenings;
    }
    
    function setAvailableOpenings($value)
    {
        $this->availableOpenings = $value;
    }
    
    function getSalary()
    {
        return $this->salary;
    }
    
    function setSalary($value)
    {
        $this->salary = $value;
    }
    
    function getCity()
    {
        return $this->city;
    }
    
    function getState()
    {
        return $this->state;
    }
    
    function getDepartmentId()
    {
        return $this->departmentId;
    }
    
    function setDepartmemtId($value)
    {
        $this->departmentId = $value;
    }
    
    function getStartDate()
    {
        return $this->startDate;
    }
    
    function setStartDate($value)
    {
        $this->startDate = $value;
    }
    
    function getEnteredBy()
    {
        return $this->enteredBy;
    }
    
    function setEnteredBy($value)
    {
        $this->enteredBy = $value;
    }
    
    function getRecruiter()
    {
        return $this->recruiter;
    }
    
    function setRecruiter($value)
    {
        $this->recruiter = $value;
    }
    
    function getOwner()
    {
        return $this->owner;
    }
    
    function setOwner($value)
    {
        $this->owner = $value;
    }
    
    function getSiteId()
    {
        return $this->siteId;
    }
    
    function getQuestionnaire()
    {
        return $this->questionnaire;
    }
    
    function setQuestionnaire($value)
    {
        $this->questionnaire = $value;
    }
    
    static function create(
        $siteId,
        $title,
        $companyId,
        $contactID,
        $description,
        $notes,
        $duration,
        $maxRate,
        $type,
        $isHot,
        $public,
        $openings,
        $companyJobId,
        $salary,
        $city,
        $state,
        $startDate,
        $enteredBy,
        $recruiter,
        $owner,
        $departmentId,
        $questionnaire
    ) {
        $instance = new JobOrder(
            $siteId,
            $title,
            $type,
            $status = 0,
            $city,
            $state,
            $enteredBy,
            $public
        );
        $instance->setCompanyJobId($companyJobId);
        $instance->setCompanyId($companyId);
        $instance->setContactId($contactID);
        $instance->setDescription($description);
        $instance->setNotes($notes);
        $instance->setDuration($duration);
        $instance->setMaxRate($maxRate);
        $instance->setIsHot($isHot);
        $instance->setOpenings($openings);
        $instance->setAvailableOpenings($openings);
        $instance->setSalary($salary);
        $instance->setDepartmemtId($departmentId);
        $instance->setEnteredBy($enteredBy);
        $instance->setRecruiter($recruiter);
        $instance->setOwner($owner);
        $instance->setQuestionnaire($questionnaire);
        return $instance;
    }
}
// id
// date created
// date modified
// public
