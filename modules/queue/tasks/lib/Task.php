<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

class Task {
    protected $taskName;
    protected $taskDescription;
    protected $taskID;

    public function setTaskID($id)
    {
        $this->taskID = $id;
    }

    public function setResponse($msg)
    {
        QueueProcessor::setTaskResponse($this->taskID, $msg);
    }

    public function getName()
    {
        return $taskName;
    }

    public function setName($myName)
    {
        return ($this->taskName = $myName);
    }

    public function getDescription()
    {
        return $taskDescription;
    }

    public function setDescription($myDescription)
    {
        return ($this->taskDescription = $myDescription);
    }

    public function getDayOfMonth()
    {
        return intval(date('j'));
    }

    public function getDayOfWeek()
    {
        return intval(date('w'));
    }

    public function getMonth()
    {
        return intval(date('n'));
    }

    public function getYear()
    {
        return intval(date('Y'));
    }

    public function getHour()
    {
        return intval(date('G'));
    }

    public function getMinute()
    {
        return intval(date('i'));
    }
}


?>
