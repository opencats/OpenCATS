#### P - permitted, F - forbidden ####

@security @actions
Feature: Security using ACL - actions - GET
  In order to protect sensitive information from users who shouldd not have access to them
  All accesses in the system need to be controlled by the Access Control List

@candidates @actions
Scenario Outline: Candidate module actions - Part 1
  Given I am logged in with <accessLevel> access level
  
  When I do GET request "index.php?m=candidates&a=show"
  Then the response should <PShow> contain "Bad Server Information"
  And the response should <FShow> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=add"
  Then the response should <PAdd> contain "Candidates: Add Candidate"
  And the response should <FAdd> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=edit"
  Then the response should <PEdit> contain "Bad Server Information"
  And the response should <FEdit> contain "You don't have permission"
 
  When I do GET request "index.php?m=candidates&a=delete"
  Then the response should <PDelete> contain "Bad Server Information"
  And the response should <FDelete> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=search"
  Then the response should <PSearch> contain "Candidates: Search Candidates"
  And the response should <FSearch> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=search&getback=getback"
  Then the response should <PSearch> contain "No wild card string specified."
  And the response should <FSearch> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=viewResume"
  Then the response should <PViewResume> contain "Bad Server Information"
  And the response should <FViewResume> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=considerForJobSearch"
  Then the response should <PAddToPipeline> contain "Bad Server Information"
  And the response should <FAddToPipeline> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=addToPipeline"
  Then the response should <PAddToPipeline> contain "Bad Server Information"
  And the response should <FAddToPipeline> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=addCandidateTags"
  Then the response should <PAddCandidateTags> contain "Bad Server Information"
  And the response should <FAddCandidateTags> contain "You don't have permission"
  
  Examples:
  | accessLevel | PShow   | FShow   | PAdd    | FAdd    | PEdit   | FEdit   | PDelete | FDelete | PSearch | FSearch | PViewResume | FViewResume | PAddToPipeline | FAddToPipeline | PAddCandidateTags | FAddCandidateTags | 
  | DISABLED    | not     | not     | not     | not     | not     | not     | not     | not     | not     | not     | not         | not         | not            | not            | not               | not               |
  | READONLY    |         | not     | not     |         | not     |         | not     |         |         | not     |             | not         | not            |                | not               |                   |
  | EDIT        |         | not     |         | not     |         | not     | not     |         |         | not     |             | not         |                | not            |                   | not               |
  | DELETE      |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         |                | not            |                   | not               |
  | DEMO        |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         |                | not            |                   | not               |
  | ADMIN       |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         |                | not            |                   | not               |
  | MULTI_ADMIN |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         |                | not            |                   | not               |
  | ROOT        |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         |                | not            |                   | not               |
  
  @candidates @actions
  Scenario Outline: Candidate module actions - Part 2
  Given I am logged in with <accessLevel> access level
  
  When I do GET request "index.php?m=candidates&a=addActivityChangeStatus"
  Then the response should <PAddStatus> contain "Bad Server Information"
  And the response should <FAddStatus> contain "You don't have permission"
 
  When I do GET request "index.php?m=candidates&a=removeFromPipeline"
  Then the response should <PRemoveFromPipe> contain "Bad Server Information"
  And the response should <FRemoveFromPipe> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=addEditImage"
  Then the response should <PAddEditImage> contain "Bad Server Information"
  And the response should <FAddEditImage> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=createAttachment"
  Then the response should <PCreateAttch> contain "Bad Server Information"
  And the response should <FCreateAttch> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=administrativeHideShow"
  Then the response should <PAdminHideShow> contain "Bad Server Information"
  And the response should <FAdminHideShow> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=deleteAttachment"
  Then the response should <PDeleteAttch> contain "Bad Server Information"
  And the response should <FDeleteAttch> contain "You don't have permission"
  
  #When I do GET request "index.php?m=candidates&a=savedLists"
  #Then the response should <PSavedLists> contain "Bad Server Information"
  #And the response should <FSavedLists> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=emailCandidates"
  Then the response should <PEmail> contain "Required Fields are Missing"
  And the response should <FEmail> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=show_questionnaire"
  Then the response should <PShowQuest> contain "Bad Server Information"
  And the response should <FShowQuest> contain "You don't have permission"
  
  When I do GET request "index.php?m=candidates&a=listByView"
  Then the response should <PListByView> contain "Candidates: Home"
  And the response should <FListByView> contain "You don't have permission"
 
 Examples:
  | accessLevel | PAddStatus | FAddStatus | PRemoveFromPipe | FRemoveFromPipe | PAddEditImage | FAddEditImage | PCreateAttch | FCreateAttch | PAdminHideShow | FAdminHideShow | PDeleteAttch | FDeleteAttch | PEmail | FEmail | PShowQuest | FShowQuest | PListByView | FListByView | 
  | DISABLED    | not        | not        | not             | not             | not           | not           | not          | not          | not            | not            | not          | not          | not    | not    | not        | not        | not         | not         |
  | READONLY    | not        |            | not             |                 | not           |               | not          |              | not            |                | not          |              |        | not    |            | not        |             | not         |
  | EDIT        |            | not        | not             |                 |               | not           |              | not          | not            |                | not          |              |        | not    |            | not        |             | not         |
  | DELETE      |            | not        |                 | not             |               | not           |              | not          | not            |                |              | not          |        | not    |            | not        |             | not         |
  | DEMO        |            | not        |                 | not             |               | not           |              | not          | not            |                |              | not          | not    |        |            | not        |             | not         |
  | ADMIN       |            | not        |                 | not             |               | not           |              | not          | not            |                |              | not          |        | not    |            | not        |             | not         |
  | MULTI_ADMIN |            | not        |                 | not             |               | not           |              | not          |                | not            |              | not          |        | not    |            | not        |             | not         |
  | ROOT        |            | not        |                 | not             |               | not           |              | not          |                | not            |              | not          |        | not    |            | not        |             | not         |


@joborders @actions
Scenario Outline: Job Order module actions - Part 1
  Given I am logged in with <accessLevel> access level
  
  When I do GET request "index.php?m=joborders&a=show"
  Then the response should <PShow> contain "Bad Server Information"
  And the response should <FShow> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=addJobOrderPopup"
  Then the response should <PAdd> contain "Job Orders: Add Job Order"
  And the response should <FAdd> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=add"
  Then the response should <PAdd> contain "Job Orders: Add Job Order"
  And the response should <FAdd> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=addCandidateModal"
  Then the response should <PAdd> contain "Bad Server Information"
  And the response should <FAdd> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=edit"
  Then the response should <PEdit> contain "Bad Server Information"
  And the response should <FEdit> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=delete"
  Then the response should <PDelete> contain "Bad Server Information"
  And the response should <FDelete> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=search"
  Then the response should <PSearch> contain "Job Orders: Search Job Orders"
  And the response should <FSearch> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=search&getback=getback"
  Then the response should <PSearch> contain "No wild card string specified."
  And the response should <FSearch> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=addActivityChangeStatus"
  Then the response should <PAddStatus> contain "Bad Server Information"
  And the response should <FAddStatus> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=administrativeHideShow"
  Then the response should <PAdminHideShow> contain "Bad Server Information"
  And the response should <FAdminHideShow> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=listByView"
  Then the response should <PListByView> contain "Job Orders: Home"
  And the response should <FListByView> contain "You don't have permission"
  
 Examples:
  | accessLevel | PShow   | FShow   | PAdd    | FAdd    | PEdit   | FEdit   | PDelete | FDelete | PSearch | FSearch | PAddStatus  | FAddStatus  | PAdminHideShow | FAdminHideShow | PListByView | FListByView | 
  | DISABLED    | not     | not     | not     | not     | not     | not     | not     | not     | not     | not     | not         | not         | not            | not            | not         | not         |
  | READONLY    |         | not     | not     |         | not     |         | not     |         |         | not     | not         |             | not            |                |             | not         |
  | EDIT        |         | not     |         | not     |         | not     | not     |         |         | not     |             | not         | not            |                |             | not         |
  | DELETE      |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         | not            |                |             | not         |
  | DEMO        |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         | not            |                |             | not         |
  | ADMIN       |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         | not            |                |             | not         |
  | MULTI_ADMIN |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         |                | not            |             | not         |
  | ROOT        |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         |                | not            |             | not         |
  
 @joborders @actions
 Scenario Outline: Job Order module actions - Part 2
  Given I am logged in with <accessLevel> access level
  
  When I do GET request "index.php?m=joborders&a=considerCandidateSearch"
  Then the response should <PConsiderCandidate> contain "Bad Server Information"
  And the response should <FConsiderCandidate> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=addToPipeline"
  Then the response should <PAddToPipe> contain "Bad Server Information"
  And the response should <FAddToPipe> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=removeFromPipeline"
  Then the response should <PRemoveFromPipe> contain "Bad Server Information"
  And the response should <FRemoveFromPipe> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=createAttachment"
  Then the response should <PCreateAttch> contain "Bad Server Information"
  And the response should <FCreateAttch> contain "You don't have permission"
  
  When I do GET request "index.php?m=joborders&a=deleteAttachment"
  Then the response should <PDeleteAttch> contain "Bad Server Information"
  And the response should <FDeleteAttch> contain "You don't have permission"
  
  #handling function does not exist
  #When I do GET request "index.php?m=joborders&a=setCandidateJobOrder"
  #Then the response should <PSetCandJO> contain "Bad Server Information"
  #And the response should <FSetCandJO> contain "You don't have permission"
  
  Examples:
  | accessLevel | PConsiderCandidate   | FConsiderCandidate   | PAddToPipe    | FAddToPipe    | PRemoveFromPipe   | FRemoveFromPipe   | PCreateAttch | FCreateAttch | PDeleteAttch | FDeleteAttch |  
  | DISABLED    | not                  | not                  | not           | not           | not               | not               | not          | not          | not          | not          |  
  | READONLY    | not                  |                      | not           |               | not               |                   | not          |              | not          |              |  
  | EDIT        |                      | not                  |               | not           | not               |                   |              | not          | not          |              |  
  | DELETE      |                      | not                  |               | not           |                   | not               |              | not          |              | not          |  
  | DEMO        |                      | not                  |               | not           |                   | not               |              | not          |              | not          |  
  | ADMIN       |                      | not                  |               | not           |                   | not               |              | not          |              | not          |  
  | MULTI_ADMIN |                      | not                  |               | not           |                   | not               |              | not          |              | not          |  
  | ROOT        |                      | not                  |               | not           |                   | not               |              | not          |              | not          |  
  
 @companies @actions
 Scenario Outline: Companies module actions
  Given I am logged in with <accessLevel> access level
  
  When I do GET request "index.php?m=companies&a=show"
  Then the response should <PShow> contain "Invalid company ID."
  And the response should <FShow> contain "Invalid user level for action."
  
  When I do GET request "index.php?m=companies&a=internalPostings"
  Then the response should <PInternalPost> contain "Companies: Company Details"
  And the response should <FInternalPost> contain "Invalid user level for action."
  
  When I do GET request "index.php?m=companies&a=add"
  Then the response should <PAdd> contain "Companies: Add Company"
  And the response should <FAdd> contain "Invalid user level for action."
  
  When I do GET request "index.php?m=companies&a=edit"
  Then the response should <PEdit> contain "Invalid company ID."
  And the response should <FEdit> contain "Invalid user level for action."
  
  When I do GET request "index.php?m=companies&a=delete"
  Then the response should <PDelete> contain "Invalid company ID."
  And the response should <FDelete> contain "Invalid user level for action."
  
  When I do GET request "index.php?m=companies&a=search"
  Then the response should <PSearch> contain "Companies: Search Companies"
  And the response should <FSearch> contain "Invalid user level for action"
  
  When I do GET request "index.php?m=companies&a=search&getback=getback"
  Then the response should <PSearch> contain "No wild card string specified."
  And the response should <FSearch> contain "Invalid user level for action"
  
  When I do GET request "index.php?m=companies&a=listByView"
  Then the response should <PListByView> contain "Companies: Home"
  And the response should <FListByView> contain "Invalid user level for action"
    
  When I do GET request "index.php?m=companies&a=createAttachment"
  Then the response should <PCreateAttch> contain "Bad Server Information"
  And the response should <FCreateAttch> contain "Invalid user level for action"
  
  When I do GET request "index.php?m=companies&a=deleteAttachment"
  Then the response should <PDeleteAttch> contain "Bad Server Information"
  And the response should <FDeleteAttch> contain "Invalid user level for action"

Examples:
  | accessLevel | PShow   | FShow   | PInternalPost | FInternalPost | PAdd    | FAdd    | PEdit   | FEdit   | PDelete | FDelete | PSearch | FSearch | PListByView | FListByView | PCreateAttch | FCreateAttch | PDeleteAttch | FDeleteAttch |  
  | DISABLED    | not     | not     | not           | not           | not     | not     | not     | not     | not     | not     | not     | not     | not         | not         | not          | not          | not          | not          |  
  | READONLY    |         | not     |       	    | not           | not     |         | not     |         | not     |         |         | not     |             | not         | not          |              | not          |              |  
  | EDIT        |         | not     |       	    | not           |         | not     |         | not     | not     |         |         | not     |             | not         |              | not          | not          |              |  
  | DELETE      |         | not     |       	    | not           |         | not     |         | not     |         | not     |         | not     |             | not         |              | not          |              | not          |  
  | DEMO        |         | not     |       	    | not           |         | not     |         | not     |         | not     |         | not     |             | not         |              | not          |              | not          |  
  | ADMIN       |         | not     |       	    | not           |         | not     |         | not     |         | not     |         | not     |             | not         |              | not          |              | not          |  
  | MULTI_ADMIN |         | not     |       	    | not           |         | not     |         | not     |         | not     |         | not     |             | not         |              | not          |              | not          |  
  | ROOT        |         | not     |       	    | not           |         | not     |         | not     |         | not     |         | not     |             | not         |              | not          |              | not          |  


@contacts @actions
Scenario Outline: Contacts module actions
  Given I am logged in with <accessLevel> access level
  
  When I do GET request "index.php?m=contacts&a=show"
  Then the response should <PShow> contain "Bad Server Information"
  And the response should <FShow> contain "You don't have permission"

  When I do GET request "index.php?m=contacts&a=add"
  Then the response should <PAdd> contain "Contacts: Add Contact"
  And the response should <FAdd> contain "You don't have permission"
  
  When I do GET request "index.php?m=contacts&a=edit"
  Then the response should <PEdit> contain "Bad Server Information"
  And the response should <FEdit> contain "You don't have permission"
  
  When I do GET request "index.php?m=contacts&a=delete"
  Then the response should <PDelete> contain "Bad Server Information"
  And the response should <FDelete> contain "You don't have permission"
  
  When I do GET request "index.php?m=contacts&a=search"
  Then the response should <PSearch> contain "Contacts: Search Contacts"
  And the response should <FSearch> contain "You don't have permission"
  
  When I do GET request "index.php?m=contacts&a=search&getback=getback"
  Then the response should <PSearch> contain "Wild Card String Missing"
  And the response should <FSearch> contain "You don't have permission"
  
  When I do GET request "index.php?m=contacts&a=listByView"
  Then the response should <PListByView> contain "Contacts: Home"
  And the response should <FListByView> contain "You don't have permission"
    
  When I do GET request "index.php?m=contacts&a=addActivityScheduleEvent"
  Then the response should <PAddActivity> contain "Bad Server Information"
  And the response should <FAddActivity> contain "You don't have permission"
  
  When I do GET request "index.php?m=contacts&a=showColdCallList"
  Then the response should <PShowColdCall> contain "Contacts: Cold Call List"
  And the response should <FShowColdCall> contain "You don't have permission"
  
  When I do GET request "index.php?m=contacts&a=downloadVCard"
  Then the response should <PDownloadVCard> contain "Bad Server Information"
  And the response should <FDownloadVCard> contain "You don't have permission"
  
 Examples:
  | accessLevel | PShow   | FShow   | PAdd    | FAdd    | PEdit   | FEdit   | PDelete | FDelete | PSearch | FSearch | PListByView | FListByView | PAddActivity | FAddActivity | PShowColdCall| FShowColdCall| PDownloadVCard | FDownloadVCard | 
  | DISABLED    | not     | not     | not     | not     | not     | not     | not     | not     | not     | not     | not         | not         | not          | not          | not          | not          | not            | not            | 
  | READONLY    |         | not     | not     |         | not     |         | not     |         |         | not     |             | not         | not          |              |              | not          |                | not            | 
  | EDIT        |         | not     |         | not     |         | not     | not     |         |         | not     |             | not         |              | not          |              | not          |                | not            | 
  | DELETE      |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         |              | not          |              | not          |                | not            | 
  | DEMO        |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         |              | not          |              | not          |                | not            | 
  | ADMIN       |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         |              | not          |              | not          |                | not            | 
  | MULTI_ADMIN |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         |              | not          |              | not          |                | not            | 
  | ROOT        |         | not     |         | not     |         | not     |         | not     |         | not     |             | not         |              | not          |              | not          |                | not            | 
  
@activities @actions
Scenario Outline: Activity module actions
  Given I am logged in with <accessLevel> access level
  
  When I do GET request "index.php?m=activity&a=viewByDate"
  Then the response should <PViewByDate> contain "Activities"
  And the response should <FViewByDate> contain "You don't have permission"
  
  When I do GET request "index.php?m=activity&a=listByViewDataGrid"
  Then the response should <PListByView> contain "Activities - Page"
  And the response should <FListByView> contain "You don't have permission"
  
Examples:
  | accessLevel | PViewByDate | FViewByDate | PListByView | FListByView | 
  | DISABLED    | not         | not         | not         | not         |
  | READONLY    |             | not         |             | not         |
  | EDIT        |             | not         |             | not         |
  | DELETE      |             | not         |             | not         |
  | DEMO        |             | not         |             | not         |
  | ADMIN       |             | not         |             | not         |
  | MULTI_ADMIN |             | not         |             | not         |
  | ROOT        |             | not         |             | not         |
  
@dashboard @home @actions
Scenario Outline: Home module actions
  Given I am logged in with <accessLevel> access level
  
  When I do GET request "index.php?m=home&a=quickSearch"
  Then the response should <PQuickSearch> contain "No query string specified."
  And the response should <FQuickSearch> contain "You don't have permission"
  
  When I do GET request "index.php?m=home&a=deleteSavedSearch"
  Then the response should <PDeleteSearch> contain "Bad Server Information"
  And the response should <FDeleteSearch> contain "You don't have permission"
  
  When I do GET request "index.php?m=home&a=addSavedSearch"
  Then the response should <PAddSearch> contain "Bad Server Information"
  And the response should <FAddSearch> contain "You don't have permission"
  
  #handling function is undefined
  #When I do GET request "index.php?m=home&a=getAttachment"
  #Then the response should <PGetAttachment> contain "Bad Server Information"
  #And the response should <FGetAttachment> contain "You don't have permission
  
  When I do GET request "index.php?m=home&a=home"
  Then the response should <PHome> contain "My Recent Calls"
  And the response should <FHome> contain "You don't have permission"
  
 Examples:
  | accessLevel | PQuickSearch | FQuickSearch | PDeleteSearch | FDeleteSearch | PAddSearch  | FAddSearch    | PHome   | FHome   |
  | DISABLED    | not          | not          | not           | not           | not         | not           | not     | not     |
  | READONLY    |              | not          |               | not           |             | not           |         | not     |
  | EDIT        |              | not          |               | not           |             | not           |         | not     |
  | DELETE      |              | not          |               | not           |             | not           |         | not     |
  | DEMO        |              | not          |               | not           |             | not           |         | not     |
  | ADMIN       |              | not          |               | not           |             | not           |         | not     |
  | MULTI_ADMIN |              | not          |               | not           |             | not           |         | not     |
  | ROOT        |              | not          |               | not           |             | not           |         | not     |
  
@lists @actions
Scenario Outline: Lists module actions
  Given I am logged in with <accessLevel> access level
  
  #Handling function is undefined
  #When I do GET request "index.php?m=lists&a=show"
  #Then the response should <PShow> contain "Bad Server Information"
  #And the response should <FShow> contain "You don't have permission"
  
  When I do GET request "index.php?m=lists&a=showList"
  Then the response should <PShowList> contain "Bad Server Information"
  And the response should <FShowList> contain "You don't have permission"
  
  When I do GET request "index.php?m=lists&a=quickActionAddToListModal"
  Then the response should <PAddToList> contain "Bad Server Information"
  And the response should <FAddToList> contain "You don't have permission"
  
  When I do GET request "index.php?m=lists&a=addToListFromDatagridModal"
  Then the response should <PAddToList> contain "Bad Server Information"
  And the response should <FAddToList> contain "You don't have permission"
  
  When I do GET request "index.php?m=lists&a=removeFromListDatagrid"
  Then the response should <PRemoveFromList> contain "Bad Server Information"
  And the response should <FRemoveFromList> contain "You don't have permission"
  
  When I do GET request "index.php?m=lists&a=deleteStaticList"
  Then the response should <PDeleteList> contain "Bad Server Information"
  And the response should <FDeleteList> contain "You don't have permission"
  
  When I do GET request "index.php?m=lists&a=listByView"
  Then the response should <PListByView> contain "Lists: Home"
  And the response should <FListByView> contain "You don't have permission"
  
 Examples:
  | accessLevel | PShowList | FShowList | PAddToList  | FAddToList    | PRemoveFromList | FRemoveFromList | PDeleteList | FDeleteList | PListByView | FListByView |
  | DISABLED    | not       | not       | not         | not           | not             | not             | not         | not         | not         | not         |
  | READONLY    |           | not       |             | not           |                 | not             |             | not         |             | not         |
  | EDIT        |           | not       |             | not           |                 | not             |             | not         |             | not         |
  | DELETE      |           | not       |             | not           |                 | not             |             | not         |             | not         |
  | DEMO        |           | not       |             | not           |                 | not             |             | not         |             | not         |
  | ADMIN       |           | not       |             | not           |                 | not             |             | not         |             | not         |
  | MULTI_ADMIN |           | not       |             | not           |                 | not             |             | not         |             | not         |
  | ROOT        |           | not       |             | not           |                 | not             |             | not         |             | not         |
  
 
@calendar @actions
Scenario Outline: Calendar module actions
  Given I am logged in with <accessLevel> access level
  
  When I do GET request "index.php?m=calendar&a=dynamicData"
  Then the response should <PDynamicData> contain "Invalid Information"
  And the response should <FDynamicData> contain "You don't have permission"
  
  When I do GET request "index.php?m=calendar&a=deleteEvent"
  Then the response should <PDeleteEvent> contain "Bad Server Information"
  And the response should <FDeleteEvent> contain "You don't have permission"
  
  When I do GET request "index.php?m=calendar&a=showCalendar"
  Then the response should <PShowCalendar> contain "My Upcoming Events / Calls"
  And the response should <FShowCalendar> contain "You don't have permission"
  
 Examples:
  | accessLevel | PDynamicData | FDynamicData | PDeleteEvent | FDeleteEvent | PShowCalendar | FShowCalendar |
  | DISABLED    | not          | not          | not          | not          | not           | not           |
  | READONLY    |              | not          | not          |              |               | not           |
  | EDIT        |              | not          | not          |              |               | not           |
  | DELETE      |              | not          |              | not          |               | not           |
  | DEMO        |              | not          |              | not          |               | not           |
  | ADMIN       |              | not          |              | not          |               | not           |
  | MULTI_ADMIN |              | not          |              | not          |               | not           |
  | ROOT        |              | not          |              | not          |               | not           |
  
@reports @actions
Scenario Outline: Reports module actions
  Given I am logged in with <accessLevel> access level
  
  When I do GET request "index.php?m=reports&a=graphView"
  Then the response should <PGraphView> contain "testdomain.com"
  And the response should <FGraphView> contain "You don't have permission"
  
  When I do GET request "index.php?m=reports&a=generateJobOrderReportPDF"
  Then the response should <PGenerateJOReport> contain "FPDF error:"
  And the response should <FGenerateJOReport> contain "You don't have permission"
  
  When I do GET request "index.php?m=reports&a=showSubmissionReport"
  Then the response should <PShowSubmRep> contain "Submissions"
  And the response should <FShowSubmRep> contain "You don't have permission"
  
  When I do GET request "index.php?m=reports&a=showPlacementReport"
  Then the response should <PShowPlacmRep> contain "Placements"
  And the response should <FShowPlacmRep> contain "You don't have permission"
  
  When I do GET request "index.php?m=reports&a=customizeJobOrderReport"
  Then the response should <PCustomizeReport> contain "Bad Server Information"
  And the response should <FCustomizeReport> contain "You don't have permission"
  
  When I do GET request "index.php?m=reports&a=customizeEEOReport"
  Then the response should <PCustomizeReport> contain "Reports: EEO Report"
  And the response should <FCustomizeReport> contain "You don't have permission"
  
  When I do GET request "index.php?m=reports&a=generateEEOReportPreview"
  Then the response should <PGenerateEEOReport> contain "Reports: EEO Report"
  And the response should <FGenerateEEOReport> contain "You don't have permission"
  
  When I do GET request "index.php?m=reports&a=reports"
  Then the response should <PReports> contain "Reports"
  And the response should <FReports> contain "You don't have permission"
  
 Examples:
  | accessLevel | PGraphView   | FGraphView   | PGenerateJOReport | FGenerateJOReport | PShowSubmRep  | FShowSubmRep    | PShowPlacmRep | FShowPlacmRep | PCustomizeReport | FCustomizeReport | PGenerateEEOReport | FGenerateEEOReport | PReports | FReports |
  | DISABLED    | not          | not          | not               | not               | not           | not             | not           | not           | not              | not              | not                | not                | not      | not      |
  | READONLY    |              | not          |                   | not               |               | not             |               | not           |                  | not              |                    | not                |          | not      |
  | EDIT        |              | not          |                   | not               |               | not             |               | not           |                  | not              |                    | not                |          | not      |
  | DELETE      |              | not          |                   | not               |               | not             |               | not           |                  | not              |                    | not                |          | not      |
  | DEMO        |              | not          |                   | not               |               | not             |               | not           |                  | not              |                    | not                |          | not      |
  | ADMIN       |              | not          |                   | not               |               | not             |               | not           |                  | not              |                    | not                |          | not      |
  | MULTI_ADMIN |              | not          |                   | not               |               | not             |               | not           |                  | not              |                    | not                |          | not      |
  | ROOT        |              | not          |                   | not               |               | not             |               | not           |                  | not              |                    | not                |          | not      |
  
 