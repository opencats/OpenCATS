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

// Add a new task to the queue processor using the following line as an example.
// Use the modules/queue/tasks/SampleRecurring.php file as a template
// QueueProcessor::registerRecurringTask('SampleRecurring');

/*************** ADD NEW TASKS HERE (scheduling is set inside the task) ****************/

QueueProcessor::registerRecurringTask('./modules/queue/tasks/CleanExceptions.php');

?>
