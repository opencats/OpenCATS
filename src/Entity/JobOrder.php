<?php

namespace OpenCATS\Entity;

include_once('./lib/JobOrderStatuses.php');

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

    private $status;

    public function __construct(
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

    public function getTitle()
    {
        return $this->title;
    }

    public function getCompanyJobId()
    {
        return $this->companyJobId;
    }

    public function setCompanyJobId($value)
    {
        $this->companyJobId = $value;
    }

    public function getCompanyId()
    {
        return $this->companyId;
    }

    public function setCompanyId($value)
    {
        $this->companyId = $value;
    }

    public function getContactId()
    {
        return $this->contactId;
    }

    public function setContactId($value)
    {
        $this->contactId = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes($value)
    {
        $this->notes = $value;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($value)
    {
        $this->duration = $value;
    }

    public function getMaxRate()
    {
        return $this->maxRate;
    }

    public function setMaxRate($value)
    {
        $this->maxRate = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function isHot()
    {
        return $this->isHot;
    }

    public function setIsHot($value)
    {
        $this->isHot = $value;
    }

    public function isPublic()
    {
        return $this->isPublic;
    }

    public function getOpenings()
    {
        return $this->openings;
    }

    public function setOpenings($value)
    {
        $this->openings = $value;
    }

    public function getAvailableOpenings()
    {
        return $this->availableOpenings;
    }

    public function setAvailableOpenings($value)
    {
        $this->availableOpenings = $value;
    }

    public function getSalary()
    {
        return $this->salary;
    }

    public function setSalary($value)
    {
        $this->salary = $value;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getDepartmentId()
    {
        return $this->departmentId;
    }

    public function setDepartmemtId($value)
    {
        $this->departmentId = $value;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($value)
    {
        $this->startDate = $value;
    }

    public function getEnteredBy()
    {
        return $this->enteredBy;
    }

    public function setEnteredBy($value)
    {
        $this->enteredBy = $value;
    }

    public function getRecruiter()
    {
        return $this->recruiter;
    }

    public function setRecruiter($value)
    {
        $this->recruiter = $value;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner($value)
    {
        $this->owner = $value;
    }

    public function getSiteId()
    {
        return $this->siteId;
    }

    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    public function setQuestionnaire($value)
    {
        $this->questionnaire = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public static function create(
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
            $status = \JobOrderStatuses::getDefaultStatus(),
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
