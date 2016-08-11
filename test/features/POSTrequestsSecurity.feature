#### P - permitted, F - forbidden ####

@security @actions
Feature: Security using ACL - actions - POST
  In order to protect sensitive information from users who should not have access to them
  All accesses in the system need to be controlled by the Access Control List

@candidates @actions
Scenario Outline: Candidate module actions - Part 1
  Given I am logged in with <accessLevel> access level

  When I do POST request "index.php?m=candidates&a=add"
  Then the response should <PAdd> contain "Required Fields are Missing"
  And the response should <FAdd> contain "You don't have permission"
  
  When I do POST request "index.php?m=candidates&a=edit"
  Then the response should <PEdit> contain "Bad Server Information"
  And the response should <FEdit> contain "You don't have permission"
  
  When I do POST request "index.php?m=candidates&a=addCandidateTags"
  Then the response should <PAddCandidateTags> contain "Bad Server Information"
  And the response should <FAddCandidateTags> contain "You don't have permission"
  
  When I do POST request "index.php?m=candidates&a=addActivityChangeStatus"
  Then the response should <PAddStatus> contain "Bad Server Information"
  And the response should <FAddStatus> contain "You don't have permission"
  
  When I do POST request "index.php?m=candidates&a=addEditImage"
  Then the response should <PAddEditImage> contain "Bad Server Information"
  And the response should <FAddEditImage> contain "You don't have permission"
  
  When I do POST request "index.php?m=candidates&a=createAttachment"
  Then the response should <PCreateAttch> contain "Bad Server Information"
  And the response should <FCreateAttch> contain "You don't have permission"
  
Examples:
  | accessLevel | PAdd    | FAdd    | PEdit   | FEdit   | PAddCandidateTags | FAddCandidateTags | PAddStatus | FAddStatus | PAddEditImage | FAddEditImage | PCreateAttch | FCreateAttch |  
  | DISABLED    | not     | not     | not     | not     | not               | not               | not        | not        | not           | not           | not          | not          |
  | READONLY    | not     |         | not     |         | not               |                   | not        |            | not           |               | not          |              |
  | EDIT        |         | not     |         | not     |                   | not               |            | not        |               | not           |              | not          |
  | DELETE      |         | not     |         | not     |                   | not               |            | not        |               | not           |              | not          |
  | DEMO        |         | not     |         | not     |                   | not               |            | not        |               | not           |              | not          |
  | ADMIN       |         | not     |         | not     |                   | not               |            | not        |               | not           |              | not          |
  | MULTI_ADMIN |         | not     |         | not     |                   | not               |            | not        |               | not           |              | not          |
  | ROOT        |         | not     |         | not     |                   | not               |            | not        |               | not           |              | not          |
  
  
 
@joborders @actions
Scenario Outline: Job Order module actions
  Given I am logged in with <accessLevel> access level
  
  When I do POST request "index.php?m=joborders&a=add"
  Then the response should <PAdd> contain "Bad Server Information"
  And the response should <FAdd> contain "You don't have permission"
  
  When I do POST request "index.php?m=joborders&a=addCandidateModal"
  Then the response should <PAdd> contain "Bad Server Information"
  And the response should <FAdd> contain "You don't have permission"
  
  When I do POST request "index.php?m=joborders&a=edit"
  Then the response should <PEdit> contain "Bad Server Information"
  And the response should <FEdit> contain "You don't have permission"
  
  When I do POST request "index.php?m=joborders&a=addActivityChangeStatus"
  Then the response should <PAddStatus> contain "Bad Server Information"
  And the response should <FAddStatus> contain "You don't have permission"
  
  When I do POST request "index.php?m=joborders&a=considerCandidateSearch"
  Then the response should <PConsiderCandidate> contain "Bad Server Information"
  And the response should <FConsiderCandidate> contain "You don't have permission"
  
  When I do POST request "index.php?m=joborders&a=createAttachment"
  Then the response should <PCreateAttch> contain "Bad Server Information"
  And the response should <FCreateAttch> contain "You don't have permission"
  
 Examples:
  | accessLevel | PAdd    | FAdd    | PEdit   | FEdit   | PAddStatus  | FAddStatus | PConsiderCandidate   | FConsiderCandidate   | PCreateAttch | FCreateAttch |  
  | DISABLED    | not     | not     | not     | not     | not         | not        | not                  | not                  | not          | not          |  
  | READONLY    | not     |         | not     |         | not         |            | not                  |                      | not          |              |   
  | EDIT        |         | not     |         | not     |             | not        |                      | not                  |              | not          |  
  | DELETE      |         | not     |         | not     |             | not        |                      | not                  |              | not          |  
  | DEMO        |         | not     |         | not     |             | not        |                      | not                  |              | not          |  
  | ADMIN       |         | not     |         | not     |             | not        |                      | not                  |              | not          |  
  | MULTI_ADMIN |         | not     |         | not     |             | not        |                      | not                  |              | not          |  
  | ROOT        |         | not     |         | not     |             | not        |                      | not                  |              | not          |  
  
@companies @actions
Scenario Outline: Companies module actions
  Given I am logged in with <accessLevel> access level
  
  When I do POST request "index.php?m=companies&a=add"
  Then the response should <PAdd> contain "Required fields are missing."
  And the response should <FAdd> contain "Invalid user level for action"
  
  When I do POST request "index.php?m=companies&a=edit"
  Then the response should <PEdit> contain "Invalid company ID."
  And the response should <FEdit> contain "Invalid user level for action"
  
  When I do POST request "index.php?m=companies&a=createAttachment"
  Then the response should <PCreateAttch> contain "Bad Server Information"
  And the response should <FCreateAttch> contain "Invalid user level for action"

Examples:
  | accessLevel | PAdd    | FAdd    | PEdit   | FEdit   | PCreateAttch | FCreateAttch |  
  | DISABLED    | not     | not     | not     | not     | not          | not          |  
  | READONLY    | not     |         | not     |         | not          |              |  
  | EDIT        |         | not     |         | not     |              | not          |  
  | DELETE      |         | not     |         | not     |              | not          |  
  | DEMO        |         | not     |         | not     |              | not          |  
  | ADMIN       |         | not     |         | not     |              | not          |  
  | MULTI_ADMIN |         | not     |         | not     |              | not          |  
  | ROOT        |         | not     |         | not     |              | not          |  
  
@contacts @actions
Scenario Outline: Contacts module actions
  Given I am logged in with <accessLevel> access level
  
  When I do POST request "index.php?m=contacts&a=add"
  Then the response should <PAdd> contain "Bad Server Information"
  And the response should <FAdd> contain "You don't have permission"
  
  When I do POST request "index.php?m=contacts&a=edit"
  Then the response should <PEdit> contain "Bad Server Information"
  And the response should <FEdit> contain "You don't have permission"
  
  When I do POST request "index.php?m=contacts&a=addActivityScheduleEvent"
  Then the response should <PAddActivity> contain "Bad Server Information"
  And the response should <FAddActivity> contain "You don't have permission"
 
Examples:
  | accessLevel | PAdd    | FAdd    | PEdit   | FEdit   | PAddActivity | FAddActivity | 
  | DISABLED    | not     | not     | not     | not     | not          | not          | 
  | READONLY    | not     |         | not     |         | not          |              | 
  | EDIT        |         | not     |         | not     |              | not          | 
  | DELETE      |         | not     |         | not     |              | not          | 
  | DEMO        |         | not     |         | not     |              | not          | 
  | ADMIN       |         | not     |         | not     |              | not          | 
  | MULTI_ADMIN |         | not     |         | not     |              | not          | 
  | ROOT        |         | not     |         | not     |              | not          | 
  
@activities @actions
Scenario Outline: Activity module actions
  Given I am logged in with <accessLevel> access level

  When I do POST request "index.php?m=activity&a=viewByDate"
  Then the response should <PViewByDate> contain "Activities"
  And the response should <FViewByDate> contain "You don't have permission"

Examples:
  | accessLevel | PViewByDate | FViewByDate | 
  | DISABLED    | not         | not         |
  | READONLY    |             | not         |
  | EDIT        |             | not         |
  | DELETE      |             | not         |
  | DEMO        |             | not         |
  | ADMIN       |             | not         |
  | MULTI_ADMIN |             | not         |
  | ROOT        |             | not         |
  
@calendar @actions
Scenario Outline: Calendar module actions
  Given I am logged in with <accessLevel> access level
  
  When I do POST request "index.php?m=calendar&a=addEvent"
  Then the response should <PAddEvent> contain "Invalid Information"
  And the response should <FAddEvent> contain "You don't have permission"
  
  When I do POST request "index.php?m=calendar&a=editEvent"
  Then the response should <PEditEvent> contain "Bad Server Information"
  And the response should <FEditEvent> contain "You don't have permission"
  
 Examples:
  | accessLevel | PAddEvent | FAddEvent | PEditEvent | FEditEvent |
  | DISABLED    | not       | not       | not        | not        |
  | READONLY    | not       |           | not        |            |
  | EDIT        |           | not       |            | not        |
  | DELETE      |           | not       |            | not        |
  | DEMO        |           | not       |            | not        |
  | ADMIN       |           | not       |            | not        |
  | MULTI_ADMIN |           | not       |            | not        |
  | ROOT        |           | not       |            | not        |
  
 #### the other modules don't use POST requests ####
  