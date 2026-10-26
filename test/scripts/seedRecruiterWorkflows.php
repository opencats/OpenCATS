<?php
/* Assessment-only synthetic seed, following the existing SQL fixture mechanism.
 * Run once in the explicitly isolated cats_test container, never the main app. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (getenv('OPENCATS_WORKFLOW_DISPOSABLE') !== '1')
    throw new RuntimeException('A disposable workflow assessment is required.');
chdir(dirname(__DIR__, 2));
require './config.php';
if (DATABASE_HOST !== 'opencatsdb' || DATABASE_NAME !== 'cats_test')
    throw new RuntimeException('Only the isolated test configuration is supported.');
require './constants.php';
require './lib/DatabaseConnection.php';
$db = DatabaseConnection::getInstance();
if ($db->getAssoc('SELECT COUNT(*) AS n FROM company WHERE company_id >= 970001')['n'] != 0)
    throw new RuntimeException('Assessment records already exist; use a fresh disposable database.');
$queries = array(
    "UPDATE user SET password = MD5('opencats-assessment-only'), first_name = 'Maya', last_name = 'Patel', column_preferences = NULL WHERE user_id = 10003 AND access_level = 200",
    "UPDATE user SET password = MD5('opencats-assessment-only'), first_name = 'Theo', last_name = 'Grant', column_preferences = NULL WHERE user_id = 10002 AND access_level = 100",
    "UPDATE site SET name = 'Recruiter assessment - synthetic only', date_format_ddmmyy = 1, time_format_24 = 1, time_zone = 2, first_time_setup = 0, localization_configured = 1, agreed_to_license = 1",
    "INSERT INTO company (company_id,name,city,state,country,owner,entered_by,commercial_tier,relationship_status,date_created,date_modified) VALUES
        (970001,'Northbridge Precision','Leeds','West Yorkshire','GB',10003,10003,'B','Prospect',NOW() - INTERVAL 60 DAY,NOW()),
        (970002,'Westhaven Distribution','Bradford','West Yorkshire','GB',10003,10003,'A','Client',NOW() - INTERVAL 60 DAY,NOW())",
    "INSERT INTO contact (contact_id,company_id,first_name,last_name,title,email1,owner,entered_by,company_department_id,left_company,date_created,date_modified) VALUES
        (970001,970001,'Helen','Ward','Operations Director','helen@example.invalid',10003,10003,-1,0,NOW() - INTERVAL 40 DAY,NOW()),
        (970002,970002,'Daniel','Brooks','Warehouse Manager','daniel@example.invalid',10003,10003,-1,0,NOW() - INTERVAL 40 DAY,NOW())",
    "INSERT INTO joborder (joborder_id,company_id,contact_id,title,description,notes,type,openings,openings_available,owner,recruiter,entered_by,status,city,state,salary,rate_max,start_date,date_created,date_modified,first_actionable_at) VALUES
        (970001,970001,970001,'CNC Maintenance Engineer','Permanent role: planned maintenance, CNC fault finding, day shift.','Confirm electrical certification and notice period.','H',1,1,10003,10003,10003,'Active','Leeds','West Yorkshire','42000-46000','',CURDATE() + INTERVAL 30 DAY,NOW() - INTERVAL 14 DAY,NOW(),NOW() - INTERVAL 14 DAY),
        (970002,970002,970002,'Warehouse Operatives - three vacancies','Three contract operatives: picking, packing and safe manual handling.','Early shift 06:00; twelve-week initial assignment.','C',3,3,10003,10003,10003,'Active','Bradford','West Yorkshire','','18.50',CURDATE() + INTERVAL 14 DAY,NOW() - INTERVAL 9 DAY,NOW(),NOW() - INTERVAL 9 DAY)",
    "INSERT INTO candidate (candidate_id,first_name,last_name,email1,city,state,key_skills,notes,owner,entered_by,is_active,date_created,date_modified) VALUES
        (970001,'Alex','Morgan','alex@example.invalid','Leeds','West Yorkshire','CNC maintenance; electrical fault finding','Available after four weeks notice.',10003,10003,1,NOW(),NOW()),
        (970002,'Priya','Shah','priya@example.invalid','Leeds','West Yorkshire','CNC maintenance; mechanical repair','Discuss shift preferences.',10003,10003,1,NOW(),NOW()),
        (970003,'Sam','Reed','sam@example.invalid','Bradford','West Yorkshire','Warehouse picking; manual handling','Available for early shift.',10003,10003,1,NOW(),NOW()),
        (970004,'Jamie','Cole','jamie@example.invalid','Bradford','West Yorkshire','Warehouse packing; stock control','Requires confirmation of transport.',10003,10003,1,NOW(),NOW())",
    "INSERT INTO task (task_id,title,description,due_date,priority,status,purpose,assigned_to,data_item_type,data_item_id,created_by,date_created,date_modified) VALUES
        (970001,'Call Helen about maintenance brief','Confirm the electrical qualification requirement.',CURDATE() - INTERVAL 1 DAY,'low','open','relationship_follow_up',10003,300,970001,10003,NOW(),NOW()),
        (970002,'Confirm warehouse shift and induction','Agree induction arrangements with Daniel.',CURDATE(),'normal','open','job_follow_up',10003,400,970002,10003,NOW(),NOW()),
        (970003,'Review CNC candidate suitability','Compare experience against the electrical requirement.',CURDATE() + INTERVAL 2 DAY,'high','open','candidate_update',10003,100,970001,10003,NOW(),NOW()),
        (970004,'Check Jamie transport availability','Confirm reliable travel for the early shift.',NULL,'normal','open','candidate_update',10003,100,970004,10003,NOW(),NOW())",
    "INSERT INTO activity (data_item_id,data_item_type,type,notes,entered_by,joborder_id,date_created,date_occurred,date_modified) VALUES
        (970001,300,500,'Helen outlined a likely maintenance hire; detailed brief to follow.',10003,-1,NOW() - INTERVAL 35 DAY,NOW() - INTERVAL 35 DAY,NOW() - INTERVAL 35 DAY),
        (970002,300,300,'Daniel confirmed the initial warehouse staffing requirement.',10003,-1,NOW() - INTERVAL 30 DAY,NOW() - INTERVAL 30 DAY,NOW() - INTERVAL 30 DAY)",
    "INSERT INTO activity (data_item_id,data_item_type,type,notes,entered_by,joborder_id,date_created,date_occurred,date_modified) VALUES
        (970001,100,500,'Alex confirmed CNC fault-finding experience and availability for a technical interview.',10003,970001,NOW(),NOW(),NOW())",
    "INSERT INTO calendar_event (type,date,title,entered_by,data_item_type,data_item_id,joborder_id,public) VALUES
        (400,CURDATE()+INTERVAL 1 DAY+INTERVAL 10 HOUR,'Alex Morgan - CNC technical interview',10003,100,970001,970001,0),
        (300,CURDATE()+INTERVAL 2 DAY+INTERVAL 14 HOUR,'Warehouse induction briefing with Daniel',10003,300,970002,970002,0)"
);
if (!$db->beginTransaction()) throw new RuntimeException('Seed transaction unavailable.');
try
{
    foreach ($queries as $sql)
        if (!$db->query($sql)) throw new RuntimeException('Assessment seed failed.');
    $db->commitTransaction();
}
catch (Throwable $e)
{
    $db->rollbackTransaction();
    throw $e;
}
echo json_encode(array('recruiter' => 10003, 'readOnly' => 10002,
    'companies' => array(970001, 970002), 'contacts' => array(970001, 970002),
    'jobs' => array(970001, 970002), 'candidates' => array(970001, 970002, 970003, 970004),
    'tasks' => array(970001, 970002, 970003, 970004)), JSON_PRETTY_PRINT) . PHP_EOL;
