<?php
/*
 * OSATS
 * Asynchroneous Queue Processor
 *
 */

// Add a new task to the queue processor using the following line as an example.
// Use the modules/queue/tasks/SampleRecurring.php file as a template
// QueueProcessor::registerRecurringTask('SampleRecurring');

/*************** ADD NEW TASKS HERE (scheduling is set inside the task) ****************/

include_once('config.php');
include_once('./modules/asp/lib/ASPUtility.php');

QueueProcessor::registerRecurringTask('CleanExceptions');

// Sphinx task for updating deltas, rebuilding of the index, etc.
//if (ENABLE_SPHINX)
//    QueueProcessor::registerRecurringTask('Sphinx');

?>