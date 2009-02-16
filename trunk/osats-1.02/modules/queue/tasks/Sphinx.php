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
 *
 * QueueProcessor Task for automatically submitting web forms using
 * PHP Curl to job bulletin boards like SimplyHired or Indeed.
 *
 *
 * $Id: Sphinx.php 3539 2007-11-09 23:03:11Z andrew $
 */

include_once('./modules/queue/lib/Task.php');
include_once('./modules/asp/lib/ASPUtility.php');

class Sphinx extends Task
{
    public function getSchedule()
    {
        // every minute
        return '* * * * *';
    }

    public function run($siteID, $args)
    {
        Task::setName('Sphinx Rebuild, Delta, Status');
        Task::setDescription('Rebuilds the index, the delta and status of Sphinx indexer.');
        $response = 'The following tasks were completed successfully: ';

        // Nightly Rebuild of entire Sphinx index at 01:00AM CST
        if (self::getHour() == 1 && self::getMinute() == 0)
        {
            if (!system($script = sprintf('%s/scripts/sphinx_rotate.sh', ASPUtility::getEnvironmentValue('CATS_PATH')), $result))
            {
                $this->setResponse(sprintf('Unable to execute "%s": ', $script) . $result);
                return TASKRET_ERROR;
            }
            $response .= ' * Rebuilt the entire Sphinx index';
        }

        // Check Sphinx Status every 5 minutes
        if (!(self::getMinute() % 5))
        {
            if (!system($script = sprintf('%s %s/scripts/sphinxtest.php',
                ASPUtility::getEnvironmentValue('PHP_PATH'),
                ASPUtility::getEnvironmentValue('CATS_PATH')), $result))
            {
                $this->setResponse(sprintf('Unable to execute "%s": ', $script) . $result);
                return TASKRET_ERROR;
            }
            if (!system($script = sprintf('%s/scripts/sphinx_restart.sh',
                ASPUtility::getEnvironmentValue('CATS_PATH')), $result))
            {
                $this->setResponse(sprintf('Unable to execute "%s": ', $script) . $result);
                return TASKRET_ERROR;
            }
            $response .= ' * Checked Sphinx status';
        }

        // Update Sphinx DELTA index every minute
        if (!system($script = sprintf('%s/scripts/sphinx_update_delta.sh',
            ASPUtility::getEnvironmentValue('CATS_PATH')), $result))
        {
            $this->setResponse(sprintf('Unable to execute "%s": ', $script) . $result);
            return TASKRET_ERROR;
        }

        $response .= ' * Updated the Delta';

        $this->setResponse($response);
        return TASKRET_SUCCESS;
    }
}
