<?php
/*
 * CATS
 * Asynchroneous Queue Processor
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
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
 * $Id: Task.php 3555 2007-11-11 22:34:51Z will $
 */

class Task
{
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
