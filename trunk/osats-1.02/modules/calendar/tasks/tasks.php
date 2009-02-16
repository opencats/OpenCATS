<?php
/**
 * OSATS
 */

// Add a new task to the queue processor using the following line as an example.
// Use the modules/queue/tasks/SampleRecurring.php file as a template
// QueueProcessor::registerRecurringTask('SampleRecurring');

/*************** ADD NEW TASKS HERE (scheduling is set inside the task) ****************/

QueueProcessor::registerRecurringTask('./modules/calendar/tasks/Reminders.php');

?>