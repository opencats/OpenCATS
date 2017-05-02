@security @actions
Feature: Security using ACL - actions - GET & POST
  In order to protect sensitive information from users who shouldd not have access to them
  All accesses in the system need to be controlled by the Access Control List

@candidates @actions
Scenario Outline: Candidate module actions
  Given I am logged in with <accessLevel> access level
  
  When I do <type> request on url "<url>"
  Then I should <bool> have permission
  
  Examples:
  | accessLevel | type    | url                                              | bool    | 
  | DISABLED    | GET     | index.php?m=candidates&a=show                    | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=add                     | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=edit                    | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=delete                  | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=search                  | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=search&getback=getback  | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=viewResume              | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=considerForJobSearch    | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=addToPipeline           | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=addCandidateTags        | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=addActivityChangeStatus | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=removeFromPipeline      | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=addEditImage            | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=createAttachment        | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=administrativeHideShow  | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=deleteAttachment        | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=savedLists              | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=emailCandidates         | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=show_questionnaire      | not     |
  | DISABLED    | GET     | index.php?m=candidates&a=listByView              | not     |
  | DISABLED    | POST    | index.php?m=candidates&a=add                     | not     |
  | DISABLED    | POST    | index.php?m=candidates&a=edit                    | not     |
  | DISABLED    | POST    | index.php?m=candidates&a=addCandidateTags        | not     |
  | DISABLED    | POST    | index.php?m=candidates&a=addActivityChangeStatus | not     |
  | DISABLED    | POST    | index.php?m=candidates&a=addEditImage            | not     |
  | DISABLED    | POST    | index.php?m=candidates&a=createAttachment        | not     |
  | READONLY    | GET     | index.php?m=candidates&a=show                    |         |
  | READONLY    | GET     | index.php?m=candidates&a=add                     | not     |
  | READONLY    | GET     | index.php?m=candidates&a=edit                    | not     |
  | READONLY    | GET     | index.php?m=candidates&a=delete                  | not     |
  | READONLY    | GET     | index.php?m=candidates&a=search                  |         |
  | READONLY    | GET     | index.php?m=candidates&a=search&getback=getback  |         |
  | READONLY    | GET     | index.php?m=candidates&a=viewResume              |         |
  | READONLY    | GET     | index.php?m=candidates&a=considerForJobSearch    | not     |
  | READONLY    | GET     | index.php?m=candidates&a=addToPipeline           | not     |
  | READONLY    | GET     | index.php?m=candidates&a=addCandidateTags        | not     |
  | READONLY    | GET     | index.php?m=candidates&a=addActivityChangeStatus | not     |
  | READONLY    | GET     | index.php?m=candidates&a=removeFromPipeline      | not     |
  | READONLY    | GET     | index.php?m=candidates&a=addEditImage            | not     |
  | READONLY    | GET     | index.php?m=candidates&a=createAttachment        | not     |
  | READONLY    | GET     | index.php?m=candidates&a=administrativeHideShow  | not     |
  | READONLY    | GET     | index.php?m=candidates&a=deleteAttachment        | not     |
  | READONLY    | GET     | index.php?m=candidates&a=savedLists              |         |
  | READONLY    | GET     | index.php?m=candidates&a=emailCandidates         | not     |
  | READONLY    | GET     | index.php?m=candidates&a=show_questionnaire      |         |
  | READONLY    | GET     | index.php?m=candidates&a=listByView              |         |
  | READONLY    | POST    | index.php?m=candidates&a=add                     | not     |
  | READONLY    | POST    | index.php?m=candidates&a=edit                    | not     |
  | READONLY    | POST    | index.php?m=candidates&a=addCandidateTags        | not     |
  | READONLY    | POST    | index.php?m=candidates&a=addActivityChangeStatus | not     |
  | READONLY    | POST    | index.php?m=candidates&a=addEditImage            | not     |
  | READONLY    | POST    | index.php?m=candidates&a=createAttachment        | not     |
  | EDIT        | GET     | index.php?m=candidates&a=show                    |         |
  | EDIT        | GET     | index.php?m=candidates&a=add                     |         |
  | EDIT        | GET     | index.php?m=candidates&a=edit                    |         |
  | EDIT        | GET     | index.php?m=candidates&a=delete                  | not     |
  | EDIT        | GET     | index.php?m=candidates&a=search                  |         |
  | EDIT        | GET     | index.php?m=candidates&a=search&getback=getback  |         |
  | EDIT        | GET     | index.php?m=candidates&a=viewResume              |         |
  | EDIT        | GET     | index.php?m=candidates&a=considerForJobSearch    |         |
  | EDIT        | GET     | index.php?m=candidates&a=addToPipeline           |         |
  | EDIT        | GET     | index.php?m=candidates&a=addCandidateTags        |         |
  | EDIT        | GET     | index.php?m=candidates&a=addActivityChangeStatus |         |
  | EDIT        | GET     | index.php?m=candidates&a=removeFromPipeline      | not     |
  | EDIT        | GET     | index.php?m=candidates&a=addEditImage            |         |
  | EDIT        | GET     | index.php?m=candidates&a=createAttachment        |         |
  | EDIT        | GET     | index.php?m=candidates&a=administrativeHideShow  | not     |
  | EDIT        | GET     | index.php?m=candidates&a=deleteAttachment        | not     |
  | EDIT        | GET     | index.php?m=candidates&a=savedLists              |         |
  | EDIT        | GET     | index.php?m=candidates&a=emailCandidates         | not     |
  | EDIT        | GET     | index.php?m=candidates&a=show_questionnaire      |         |
  | EDIT        | GET     | index.php?m=candidates&a=listByView              |         |
  | EDIT        | POST    | index.php?m=candidates&a=add                     |         |
  | EDIT        | POST    | index.php?m=candidates&a=edit                    |         |
  | EDIT        | POST    | index.php?m=candidates&a=addCandidateTags        |         |
  | EDIT        | POST    | index.php?m=candidates&a=addActivityChangeStatus |         |
  | EDIT        | POST    | index.php?m=candidates&a=addEditImage            |         |
  | EDIT        | POST    | index.php?m=candidates&a=createAttachment        |         |
  | DELETE      | GET     | index.php?m=candidates&a=show                    |         |
  | DELETE      | GET     | index.php?m=candidates&a=add                     |         |
  | DELETE      | GET     | index.php?m=candidates&a=edit                    |         |
  | DELETE      | GET     | index.php?m=candidates&a=delete                  |         |
  | DELETE      | GET     | index.php?m=candidates&a=search                  |         |
  | DELETE      | GET     | index.php?m=candidates&a=search&getback=getback  |         |
  | DELETE      | GET     | index.php?m=candidates&a=viewResume              |         |
  | DELETE      | GET     | index.php?m=candidates&a=considerForJobSearch    |         |
  | DELETE      | GET     | index.php?m=candidates&a=addToPipeline           |         |
  | DELETE      | GET     | index.php?m=candidates&a=addCandidateTags        |         |
  | DELETE      | GET     | index.php?m=candidates&a=addActivityChangeStatus |         |
  | DELETE      | GET     | index.php?m=candidates&a=removeFromPipeline      |         |
  | DELETE      | GET     | index.php?m=candidates&a=addEditImage            |         |
  | DELETE      | GET     | index.php?m=candidates&a=createAttachment        |         |
  | DELETE      | GET     | index.php?m=candidates&a=administrativeHideShow  | not     |
  | DELETE      | GET     | index.php?m=candidates&a=deleteAttachment        |         |
  | DELETE      | GET     | index.php?m=candidates&a=savedLists              |         |
  | DELETE      | GET     | index.php?m=candidates&a=emailCandidates         | not     |
  | DELETE      | GET     | index.php?m=candidates&a=show_questionnaire      |         |
  | DELETE      | GET     | index.php?m=candidates&a=listByView              |         |
  | DELETE      | POST    | index.php?m=candidates&a=add                     |         |
  | DELETE      | POST    | index.php?m=candidates&a=edit                    |         |
  | DELETE      | POST    | index.php?m=candidates&a=addCandidateTags        |         |
  | DELETE      | POST    | index.php?m=candidates&a=addActivityChangeStatus |         |
  | DELETE      | POST    | index.php?m=candidates&a=addEditImage            |         |
  | DELETE      | POST    | index.php?m=candidates&a=createAttachment        |         |
  | DEMO        | GET     | index.php?m=candidates&a=show                    |         |
  | DEMO        | GET     | index.php?m=candidates&a=add                     |         |
  | DEMO        | GET     | index.php?m=candidates&a=edit                    |         |
  | DEMO        | GET     | index.php?m=candidates&a=delete                  |         |
  | DEMO        | GET     | index.php?m=candidates&a=search                  |         |
  | DEMO        | GET     | index.php?m=candidates&a=search&getback=getback  |         |
  | DEMO        | GET     | index.php?m=candidates&a=viewResume              |         |
  | DEMO        | GET     | index.php?m=candidates&a=considerForJobSearch    |         |
  | DEMO        | GET     | index.php?m=candidates&a=addToPipeline           |         |
  | DEMO        | GET     | index.php?m=candidates&a=addCandidateTags        |         |
  | DEMO        | GET     | index.php?m=candidates&a=addActivityChangeStatus |         |
  | DEMO        | GET     | index.php?m=candidates&a=removeFromPipeline      |         |
  | DEMO        | GET     | index.php?m=candidates&a=addEditImage            |         |
  | DEMO        | GET     | index.php?m=candidates&a=createAttachment        |         |
  | DEMO        | GET     | index.php?m=candidates&a=administrativeHideShow  | not     |
  | DEMO        | GET     | index.php?m=candidates&a=deleteAttachment        |         |
  | DEMO        | GET     | index.php?m=candidates&a=savedLists              |         |
  | DEMO        | GET     | index.php?m=candidates&a=emailCandidates         | not     |
  | DEMO        | GET     | index.php?m=candidates&a=show_questionnaire      |         |
  | DEMO        | GET     | index.php?m=candidates&a=listByView              |         |
  | DEMO        | POST    | index.php?m=candidates&a=add                     |         |
  | DEMO        | POST    | index.php?m=candidates&a=edit                    |         |
  | DEMO        | POST    | index.php?m=candidates&a=addCandidateTags        |         |
  | DEMO        | POST    | index.php?m=candidates&a=addActivityChangeStatus |         |
  | DEMO        | POST    | index.php?m=candidates&a=addEditImage            |         |
  | DEMO        | POST    | index.php?m=candidates&a=createAttachment        |         |
  | ADMIN       | GET     | index.php?m=candidates&a=show                    |         |
  | ADMIN       | GET     | index.php?m=candidates&a=add                     |         |
  | ADMIN       | GET     | index.php?m=candidates&a=edit                    |         |
  | ADMIN       | GET     | index.php?m=candidates&a=delete                  |         |
  | ADMIN       | GET     | index.php?m=candidates&a=search                  |         |
  | ADMIN       | GET     | index.php?m=candidates&a=search&getback=getback  |         |
  | ADMIN       | GET     | index.php?m=candidates&a=viewResume              |         |
  | ADMIN       | GET     | index.php?m=candidates&a=considerForJobSearch    |         |
  | ADMIN       | GET     | index.php?m=candidates&a=addToPipeline           |         |
  | ADMIN       | GET     | index.php?m=candidates&a=addCandidateTags        |         |
  | ADMIN       | GET     | index.php?m=candidates&a=addActivityChangeStatus |         |
  | ADMIN       | GET     | index.php?m=candidates&a=removeFromPipeline      |         |
  | ADMIN       | GET     | index.php?m=candidates&a=addEditImage            |         |
  | ADMIN       | GET     | index.php?m=candidates&a=createAttachment        |         |
  | ADMIN       | GET     | index.php?m=candidates&a=administrativeHideShow  | not     |
  | ADMIN       | GET     | index.php?m=candidates&a=deleteAttachment        |         |
  | ADMIN       | GET     | index.php?m=candidates&a=savedLists              |         |
  | ADMIN       | GET     | index.php?m=candidates&a=emailCandidates         |         |
  | ADMIN       | GET     | index.php?m=candidates&a=show_questionnaire      |         |
  | ADMIN       | GET     | index.php?m=candidates&a=listByView              |         |
  | ADMIN       | POST    | index.php?m=candidates&a=add                     |         |
  | ADMIN       | POST    | index.php?m=candidates&a=edit                    |         |
  | ADMIN       | POST    | index.php?m=candidates&a=addCandidateTags        |         |
  | ADMIN       | POST    | index.php?m=candidates&a=addActivityChangeStatus |         |
  | ADMIN       | POST    | index.php?m=candidates&a=addEditImage            |         |
  | ADMIN       | POST    | index.php?m=candidates&a=createAttachment        |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=show                    |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=add                     |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=edit                    |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=delete                  |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=search                  |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=search&getback=getback  |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=viewResume              |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=considerForJobSearch    |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=addToPipeline           |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=addCandidateTags        |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=addActivityChangeStatus |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=removeFromPipeline      |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=addEditImage            |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=createAttachment        |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=administrativeHideShow  |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=deleteAttachment        |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=savedLists              |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=emailCandidates         |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=show_questionnaire      |         |
  | MULTI_ADMIN | GET     | index.php?m=candidates&a=listByView              |         |
  | MULTI_ADMIN | POST    | index.php?m=candidates&a=add                     |         |
  | MULTI_ADMIN | POST    | index.php?m=candidates&a=edit                    |         |
  | MULTI_ADMIN | POST    | index.php?m=candidates&a=addCandidateTags        |         |
  | MULTI_ADMIN | POST    | index.php?m=candidates&a=addActivityChangeStatus |         |
  | MULTI_ADMIN | POST    | index.php?m=candidates&a=addEditImage            |         |
  | MULTI_ADMIN | POST    | index.php?m=candidates&a=createAttachment        |         |
  | ROOT        | GET     | index.php?m=candidates&a=show                    |         |
  | ROOT        | GET     | index.php?m=candidates&a=add                     |         |
  | ROOT        | GET     | index.php?m=candidates&a=edit                    |         |
  | ROOT        | GET     | index.php?m=candidates&a=delete                  |         |
  | ROOT        | GET     | index.php?m=candidates&a=search                  |         |
  | ROOT        | GET     | index.php?m=candidates&a=search&getback=getback  |         |
  | ROOT        | GET     | index.php?m=candidates&a=viewResume              |         |
  | ROOT        | GET     | index.php?m=candidates&a=considerForJobSearch    |         |
  | ROOT        | GET     | index.php?m=candidates&a=addToPipeline           |         |
  | ROOT        | GET     | index.php?m=candidates&a=addCandidateTags        |         |
  | ROOT        | GET     | index.php?m=candidates&a=addActivityChangeStatus |         |
  | ROOT        | GET     | index.php?m=candidates&a=removeFromPipeline      |         |
  | ROOT        | GET     | index.php?m=candidates&a=addEditImage            |         |
  | ROOT        | GET     | index.php?m=candidates&a=createAttachment        |         |
  | ROOT        | GET     | index.php?m=candidates&a=administrativeHideShow  |         |
  | ROOT        | GET     | index.php?m=candidates&a=deleteAttachment        |         |
  | ROOT        | GET     | index.php?m=candidates&a=savedLists              |         |
  | ROOT        | GET     | index.php?m=candidates&a=emailCandidates         |         |
  | ROOT        | GET     | index.php?m=candidates&a=show_questionnaire      |         |
  | ROOT        | GET     | index.php?m=candidates&a=listByView              |         |
  | ROOT        | POST    | index.php?m=candidates&a=add                     |         |
  | ROOT        | POST    | index.php?m=candidates&a=edit                    |         |
  | ROOT        | POST    | index.php?m=candidates&a=addCandidateTags        |         |
  | ROOT        | POST    | index.php?m=candidates&a=addActivityChangeStatus |         |
  | ROOT        | POST    | index.php?m=candidates&a=addEditImage            |         |
  | ROOT        | POST    | index.php?m=candidates&a=createAttachment        |         |
  
  

@joborders @actions
Scenario Outline: Job Order module actions
  Given I am logged in with <accessLevel> access level
  
  When I do <type> request on url "<url>"
  Then I should <bool> have permission
  
 Examples:
  | accessLevel | type    | url                                             | bool |
  | DISABLED    | GET     | index.php?m=joborders&a=show                    | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=addJobOrderPopup        | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=add                     | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=addCandidateModal       | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=edit                    | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=delete                  | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=search                  | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=search&getback=getback  | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=addActivityChangeStatus | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=administrativeHideShow  | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=listByView              | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=considerCandidateSearch | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=addToPipeline           | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=removeFromPipeline      | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=createAttachment        | not  |
  | DISABLED    | GET     | index.php?m=joborders&a=deleteAttachment        | not  |
  | DISABLED    | POST    | index.php?m=joborders&a=add                     | not  |
  | DISABLED    | POST    | index.php?m=joborders&a=addCandidateModal       | not  |
  | DISABLED    | POST    | index.php?m=joborders&a=edit                    | not  |
  | DISABLED    | POST    | index.php?m=joborders&a=addActivityChangeStatus | not  |
  | DISABLED    | POST    | index.php?m=joborders&a=considerCandidateSearch | not  |
  | DISABLED    | POST    | index.php?m=joborders&a=createAttachment        | not  |
  | READONLY    | GET     | index.php?m=joborders&a=show                    |      |
  | READONLY    | GET     | index.php?m=joborders&a=addJobOrderPopup        | not  |
  | READONLY    | GET     | index.php?m=joborders&a=add                     | not  |
  | READONLY    | GET     | index.php?m=joborders&a=addCandidateModal       | not  |
  | READONLY    | GET     | index.php?m=joborders&a=edit                    | not  |
  | READONLY    | GET     | index.php?m=joborders&a=delete                  | not  |
  | READONLY    | GET     | index.php?m=joborders&a=search                  |      |
  | READONLY    | GET     | index.php?m=joborders&a=search&getback=getback  |      |
  | READONLY    | GET     | index.php?m=joborders&a=addActivityChangeStatus | not  |
  | READONLY    | GET     | index.php?m=joborders&a=administrativeHideShow  | not  |
  | READONLY    | GET     | index.php?m=joborders&a=listByView              |      |
  | READONLY    | GET     | index.php?m=joborders&a=considerCandidateSearch | not  |
  | READONLY    | GET     | index.php?m=joborders&a=addToPipeline           | not  |
  | READONLY    | GET     | index.php?m=joborders&a=removeFromPipeline      | not  |
  | READONLY    | GET     | index.php?m=joborders&a=createAttachment        | not  |
  | READONLY    | GET     | index.php?m=joborders&a=deleteAttachment        | not  |
  | READONLY    | POST    | index.php?m=joborders&a=add                     | not  | 
  | READONLY    | POST    | index.php?m=joborders&a=addCandidateModal       | not  | 
  | READONLY    | POST    | index.php?m=joborders&a=edit                    | not  | 
  | READONLY    | POST    | index.php?m=joborders&a=addActivityChangeStatus | not  | 
  | READONLY    | POST    | index.php?m=joborders&a=considerCandidateSearch | not  | 
  | READONLY    | POST    | index.php?m=joborders&a=createAttachment        | not  | 
  | EDIT        | GET     | index.php?m=joborders&a=show                    |      |
  | EDIT        | GET     | index.php?m=joborders&a=addJobOrderPopup        |      |
  | EDIT        | GET     | index.php?m=joborders&a=add                     |      |
  | EDIT        | GET     | index.php?m=joborders&a=addCandidateModal       |      |
  | EDIT        | GET     | index.php?m=joborders&a=edit                    |      |
  | EDIT        | GET     | index.php?m=joborders&a=delete                  | not  |
  | EDIT        | GET     | index.php?m=joborders&a=search                  |      |
  | EDIT        | GET     | index.php?m=joborders&a=search&getback=getback  |      |
  | EDIT        | GET     | index.php?m=joborders&a=addActivityChangeStatus |      |
  | EDIT        | GET     | index.php?m=joborders&a=administrativeHideShow  | not  |
  | EDIT        | GET     | index.php?m=joborders&a=listByView              |      |
  | EDIT        | GET     | index.php?m=joborders&a=considerCandidateSearch |      |
  | EDIT        | GET     | index.php?m=joborders&a=addToPipeline           |      |
  | EDIT        | GET     | index.php?m=joborders&a=removeFromPipeline      | not  |
  | EDIT        | GET     | index.php?m=joborders&a=createAttachment        |      |
  | EDIT        | GET     | index.php?m=joborders&a=deleteAttachment        | not  |
  | EDIT        | POST    | index.php?m=joborders&a=add                     |      |
  | EDIT        | POST    | index.php?m=joborders&a=addCandidateModal       |      |
  | EDIT        | POST    | index.php?m=joborders&a=edit                    |      |
  | EDIT        | POST    | index.php?m=joborders&a=addActivityChangeStatus |      |
  | EDIT        | POST    | index.php?m=joborders&a=considerCandidateSearch |      |
  | EDIT        | POST    | index.php?m=joborders&a=createAttachment        |      |
  | DELETE      | GET     | index.php?m=joborders&a=show                    |      |
  | DELETE      | GET     | index.php?m=joborders&a=addJobOrderPopup        |      |
  | DELETE      | GET     | index.php?m=joborders&a=add                     |      |
  | DELETE      | GET     | index.php?m=joborders&a=addCandidateModal       |      |
  | DELETE      | GET     | index.php?m=joborders&a=edit                    |      |
  | DELETE      | GET     | index.php?m=joborders&a=delete                  |      |
  | DELETE      | GET     | index.php?m=joborders&a=search                  |      |
  | DELETE      | GET     | index.php?m=joborders&a=search&getback=getback  |      |
  | DELETE      | GET     | index.php?m=joborders&a=addActivityChangeStatus |      |
  | DELETE      | GET     | index.php?m=joborders&a=administrativeHideShow  | not  |
  | DELETE      | GET     | index.php?m=joborders&a=listByView              |      |
  | DELETE      | GET     | index.php?m=joborders&a=considerCandidateSearch |      |
  | DELETE      | GET     | index.php?m=joborders&a=addToPipeline           |      |
  | DELETE      | GET     | index.php?m=joborders&a=removeFromPipeline      |      |
  | DELETE      | GET     | index.php?m=joborders&a=createAttachment        |      |
  | DELETE      | GET     | index.php?m=joborders&a=deleteAttachment        |      |
  | DELETE      | POST    | index.php?m=joborders&a=add                     |      |
  | DELETE      | POST    | index.php?m=joborders&a=addCandidateModal       |      |
  | DELETE      | POST    | index.php?m=joborders&a=edit                    |      |
  | DELETE      | POST    | index.php?m=joborders&a=addActivityChangeStatus |      |
  | DELETE      | POST    | index.php?m=joborders&a=considerCandidateSearch |      |
  | DELETE      | POST    | index.php?m=joborders&a=createAttachment        |      |
  | DEMO        | GET     | index.php?m=joborders&a=show                    |      |
  | DEMO        | GET     | index.php?m=joborders&a=addJobOrderPopup        |      |
  | DEMO        | GET     | index.php?m=joborders&a=add                     |      |
  | DEMO        | GET     | index.php?m=joborders&a=addCandidateModal       |      |
  | DEMO        | GET     | index.php?m=joborders&a=edit                    |      |
  | DEMO        | GET     | index.php?m=joborders&a=delete                  |      |
  | DEMO        | GET     | index.php?m=joborders&a=search                  |      |
  | DEMO        | GET     | index.php?m=joborders&a=search&getback=getback  |      |
  | DEMO        | GET     | index.php?m=joborders&a=addActivityChangeStatus |      |
  | DEMO        | GET     | index.php?m=joborders&a=administrativeHideShow  | not  |
  | DEMO        | GET     | index.php?m=joborders&a=listByView              |      |
  | DEMO        | GET     | index.php?m=joborders&a=considerCandidateSearch |      |
  | DEMO        | GET     | index.php?m=joborders&a=addToPipeline           |      |
  | DEMO        | GET     | index.php?m=joborders&a=removeFromPipeline      |      |
  | DEMO        | GET     | index.php?m=joborders&a=createAttachment        |      |
  | DEMO        | GET     | index.php?m=joborders&a=deleteAttachment        |      |
  | DEMO        | POST    | index.php?m=joborders&a=add                     |      |
  | DEMO        | POST    | index.php?m=joborders&a=addCandidateModal       |      |
  | DEMO        | POST    | index.php?m=joborders&a=edit                    |      |
  | DEMO        | POST    | index.php?m=joborders&a=addActivityChangeStatus |      |
  | DEMO        | POST    | index.php?m=joborders&a=considerCandidateSearch |      |
  | DEMO        | POST    | index.php?m=joborders&a=createAttachment        |      |
  | ADMIN       | GET     | index.php?m=joborders&a=show                    |      |
  | ADMIN       | GET     | index.php?m=joborders&a=addJobOrderPopup        |      |
  | ADMIN       | GET     | index.php?m=joborders&a=add                     |      |
  | ADMIN       | GET     | index.php?m=joborders&a=addCandidateModal       |      |
  | ADMIN       | GET     | index.php?m=joborders&a=edit                    |      |
  | ADMIN       | GET     | index.php?m=joborders&a=delete                  |      |
  | ADMIN       | GET     | index.php?m=joborders&a=search                  |      |
  | ADMIN       | GET     | index.php?m=joborders&a=search&getback=getback  |      |
  | ADMIN       | GET     | index.php?m=joborders&a=addActivityChangeStatus |      |
  | ADMIN       | GET     | index.php?m=joborders&a=administrativeHideShow  | not  |
  | ADMIN       | GET     | index.php?m=joborders&a=listByView              |      |
  | ADMIN       | GET     | index.php?m=joborders&a=considerCandidateSearch |      |
  | ADMIN       | GET     | index.php?m=joborders&a=addToPipeline           |      |
  | ADMIN       | GET     | index.php?m=joborders&a=removeFromPipeline      |      |
  | ADMIN       | GET     | index.php?m=joborders&a=createAttachment        |      |
  | ADMIN       | GET     | index.php?m=joborders&a=deleteAttachment        |      |
  | ADMIN       | POST    | index.php?m=joborders&a=add                     |      |
  | ADMIN       | POST    | index.php?m=joborders&a=addCandidateModal       |      |
  | ADMIN       | POST    | index.php?m=joborders&a=edit                    |      |
  | ADMIN       | POST    | index.php?m=joborders&a=addActivityChangeStatus |      |
  | ADMIN       | POST    | index.php?m=joborders&a=considerCandidateSearch |      |
  | ADMIN       | POST    | index.php?m=joborders&a=createAttachment        |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=show                    |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=addJobOrderPopup        |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=add                     |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=addCandidateModal       |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=edit                    |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=delete                  |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=search                  |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=search&getback=getback  |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=addActivityChangeStatus |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=administrativeHideShow  |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=listByView              |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=considerCandidateSearch |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=addToPipeline           |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=removeFromPipeline      |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=createAttachment        |      |
  | MULTI_ADMIN | GET     | index.php?m=joborders&a=deleteAttachment        |      |
  | MULTI_ADMIN | POST    | index.php?m=joborders&a=add                     |      |
  | MULTI_ADMIN | POST    | index.php?m=joborders&a=addCandidateModal       |      |
  | MULTI_ADMIN | POST    | index.php?m=joborders&a=edit                    |      |
  | MULTI_ADMIN | POST    | index.php?m=joborders&a=addActivityChangeStatus |      |
  | MULTI_ADMIN | POST    | index.php?m=joborders&a=considerCandidateSearch |      |
  | MULTI_ADMIN | POST    | index.php?m=joborders&a=createAttachment        |      |
  | ROOT        | GET     | index.php?m=joborders&a=show                    |      |
  | ROOT        | GET     | index.php?m=joborders&a=addJobOrderPopup        |      |
  | ROOT        | GET     | index.php?m=joborders&a=add                     |      |
  | ROOT        | GET     | index.php?m=joborders&a=addCandidateModal       |      |
  | ROOT        | GET     | index.php?m=joborders&a=edit                    |      |
  | ROOT        | GET     | index.php?m=joborders&a=delete                  |      |
  | ROOT        | GET     | index.php?m=joborders&a=search                  |      |
  | ROOT        | GET     | index.php?m=joborders&a=search&getback=getback  |      |
  | ROOT        | GET     | index.php?m=joborders&a=addActivityChangeStatus |      |
  | ROOT        | GET     | index.php?m=joborders&a=administrativeHideShow  |      |
  | ROOT        | GET     | index.php?m=joborders&a=listByView              |      |
  | ROOT        | GET     | index.php?m=joborders&a=considerCandidateSearch |      |
  | ROOT        | GET     | index.php?m=joborders&a=addToPipeline           |      |
  | ROOT        | GET     | index.php?m=joborders&a=removeFromPipeline      |      |
  | ROOT        | GET     | index.php?m=joborders&a=createAttachment        |      |
  | ROOT        | GET     | index.php?m=joborders&a=deleteAttachment        |      |
  | ROOT        | POST    | index.php?m=joborders&a=add                     |      |
  | ROOT        | POST    | index.php?m=joborders&a=addCandidateModal       |      |
  | ROOT        | POST    | index.php?m=joborders&a=edit                    |      |
  | ROOT        | POST    | index.php?m=joborders&a=addActivityChangeStatus |      |
  | ROOT        | POST    | index.php?m=joborders&a=considerCandidateSearch |      |
  | ROOT        | POST    | index.php?m=joborders&a=createAttachment        |      |
  
 
  
 @companies @actions
 Scenario Outline: Companies module actions
  Given I am logged in with <accessLevel> access level
  
  When I do <type> request on url "<url>"
  Then I should <bool> have permission

Examples:
  | accessLevel | type | url                                        | bool |
  | DISABLED    | GET  | index.php?m=companies&a=show               | not  |
  | DISABLED    | GET  | index.php?m=companies&a=internalPostings   | not  |
  | DISABLED    | GET  | index.php?m=companies&a=add                | not  |
  | DISABLED    | GET  | index.php?m=companies&a=edit               | not  |
  | DISABLED    | GET  | index.php?m=companies&a=delete             | not  |
  | DISABLED    | GET  | index.php?m=companies&a=search             | not  |
  | DISABLED    | GET  | index.php?m=companies&a=search             | not  |
  | DISABLED    | GET  | index.php?m=companies&a=listByView         | not  |
  | DISABLED    | GET  | index.php?m=companies&a=createAttachment   | not  |
  | DISABLED    | GET  | index.php?m=companies&a=deleteAttachment   | not  |
  | DISABLED    | POST | index.php?m=companies&a=add                | not  |                                                   
  | DISABLED    | POST | index.php?m=companies&a=edit               | not  |                                                 
  | DISABLED    | POST | index.php?m=companies&a=createAttachment   | not  |         
  | READONLY    | GET  | index.php?m=companies&a=show               |      |    
  | READONLY    | GET  | index.php?m=companies&a=internalPostings   |      |    
  | READONLY    | GET  | index.php?m=companies&a=add                | not  |    
  | READONLY    | GET  | index.php?m=companies&a=edit               | not  |    
  | READONLY    | GET  | index.php?m=companies&a=delete             | not  |    
  | READONLY    | GET  | index.php?m=companies&a=search             |      |    
  | READONLY    | GET  | index.php?m=companies&a=search             |      |    
  | READONLY    | GET  | index.php?m=companies&a=listByView         |      |    
  | READONLY    | GET  | index.php?m=companies&a=createAttachment   | not  |    
  | READONLY    | GET  | index.php?m=companies&a=deleteAttachment   | not  |  
  | READONLY    | POST | index.php?m=companies&a=add                | not  |                                                                        
  | READONLY    | POST | index.php?m=companies&a=edit               | not  |                                                                      
  | READONLY    | POST | index.php?m=companies&a=createAttachment   | not  |                                                                     
  | EDIT        | GET  | index.php?m=companies&a=show               |      |    
  | EDIT        | GET  | index.php?m=companies&a=internalPostings   |      |    
  | EDIT        | GET  | index.php?m=companies&a=add                |      |    
  | EDIT        | GET  | index.php?m=companies&a=edit               |      |    
  | EDIT        | GET  | index.php?m=companies&a=delete             | not  |    
  | EDIT        | GET  | index.php?m=companies&a=search             |      |    
  | EDIT        | GET  | index.php?m=companies&a=search             |      |    
  | EDIT        | GET  | index.php?m=companies&a=listByView         |      |    
  | EDIT        | GET  | index.php?m=companies&a=createAttachment   |      |    
  | EDIT        | GET  | index.php?m=companies&a=deleteAttachment   | not  |  
  | EDIT        | POST | index.php?m=companies&a=add                |      |                                                                        
  | EDIT        | POST | index.php?m=companies&a=edit               |      |                                                                      
  | EDIT        | POST | index.php?m=companies&a=createAttachment   |      |                                                                     
  | DELETE      | GET  | index.php?m=companies&a=show               |      |
  | DELETE      | GET  | index.php?m=companies&a=internalPostings   |      |
  | DELETE      | GET  | index.php?m=companies&a=add                |      |
  | DELETE      | GET  | index.php?m=companies&a=edit               |      |
  | DELETE      | GET  | index.php?m=companies&a=delete             |      |
  | DELETE      | GET  | index.php?m=companies&a=search             |      |
  | DELETE      | GET  | index.php?m=companies&a=search             |      |
  | DELETE      | GET  | index.php?m=companies&a=listByView         |      |
  | DELETE      | GET  | index.php?m=companies&a=createAttachment   |      |
  | DELETE      | GET  | index.php?m=companies&a=deleteAttachment   |      |
  | DELETE      | POST | index.php?m=companies&a=add                |      |                                                                        
  | DELETE      | POST | index.php?m=companies&a=edit               |      |                                                                      
  | DELETE      | POST | index.php?m=companies&a=createAttachment   |      |        
  | DEMO        | GET  | index.php?m=companies&a=show               |      |
  | DEMO        | GET  | index.php?m=companies&a=internalPostings   |      |
  | DEMO        | GET  | index.php?m=companies&a=add                |      |
  | DEMO        | GET  | index.php?m=companies&a=edit               |      |
  | DEMO        | GET  | index.php?m=companies&a=delete             |      |
  | DEMO        | GET  | index.php?m=companies&a=search             |      |
  | DEMO        | GET  | index.php?m=companies&a=search             |      |
  | DEMO        | GET  | index.php?m=companies&a=listByView         |      |
  | DEMO        | GET  | index.php?m=companies&a=createAttachment   |      |
  | DEMO        | GET  | index.php?m=companies&a=deleteAttachment   |      |
  | DEMO        | POST | index.php?m=companies&a=add                |      |                                                                        
  | DEMO        | POST | index.php?m=companies&a=edit               |      |                                                                      
  | DEMO        | POST | index.php?m=companies&a=createAttachment   |      |                                                                     
  | ADMIN       | GET  | index.php?m=companies&a=show               |      |
  | ADMIN       | GET  | index.php?m=companies&a=internalPostings   |      |
  | ADMIN       | GET  | index.php?m=companies&a=add                |      |
  | ADMIN       | GET  | index.php?m=companies&a=edit               |      |
  | ADMIN       | GET  | index.php?m=companies&a=delete             |      |
  | ADMIN       | GET  | index.php?m=companies&a=search             |      |
  | ADMIN       | GET  | index.php?m=companies&a=search             |      |
  | ADMIN       | GET  | index.php?m=companies&a=listByView         |      |
  | ADMIN       | GET  | index.php?m=companies&a=createAttachment   |      |
  | ADMIN       | GET  | index.php?m=companies&a=deleteAttachment   |      |
  | ADMIN       | POST | index.php?m=companies&a=add                |      |                                                                        
  | ADMIN       | POST | index.php?m=companies&a=edit               |      |                                                                      
  | ADMIN       | POST | index.php?m=companies&a=createAttachment   |      |          
  | MULTI_ADMIN | GET  | index.php?m=companies&a=show               |      |
  | MULTI_ADMIN | GET  | index.php?m=companies&a=internalPostings   |      |
  | MULTI_ADMIN | GET  | index.php?m=companies&a=add                |      |
  | MULTI_ADMIN | GET  | index.php?m=companies&a=edit               |      |
  | MULTI_ADMIN | GET  | index.php?m=companies&a=delete             |      |
  | MULTI_ADMIN | GET  | index.php?m=companies&a=search             |      |
  | MULTI_ADMIN | GET  | index.php?m=companies&a=search             |      |
  | MULTI_ADMIN | GET  | index.php?m=companies&a=listByView         |      |
  | MULTI_ADMIN | GET  | index.php?m=companies&a=createAttachment   |      |
  | MULTI_ADMIN | GET  | index.php?m=companies&a=deleteAttachment   |      |
  | MULTI_ADMIN | POST | index.php?m=companies&a=add                |      |                                                                        
  | MULTI_ADMIN | POST | index.php?m=companies&a=edit               |      |                                                                      
  | MULTI_ADMIN | POST | index.php?m=companies&a=createAttachment   |      |                                                                     
  | ROOT        | GET  | index.php?m=companies&a=show               |      |
  | ROOT        | GET  | index.php?m=companies&a=internalPostings   |      |
  | ROOT        | GET  | index.php?m=companies&a=add                |      |
  | ROOT        | GET  | index.php?m=companies&a=edit               |      |
  | ROOT        | GET  | index.php?m=companies&a=delete             |      |
  | ROOT        | GET  | index.php?m=companies&a=search             |      |
  | ROOT        | GET  | index.php?m=companies&a=search             |      |
  | ROOT        | GET  | index.php?m=companies&a=listByView         |      |
  | ROOT        | GET  | index.php?m=companies&a=createAttachment   |      |
  | ROOT        | GET  | index.php?m=companies&a=deleteAttachment   |      |
  | ROOT        | POST | index.php?m=companies&a=add                |      |                                                                        
  | ROOT        | POST | index.php?m=companies&a=edit               |      |                                                                      
  | ROOT        | POST | index.php?m=companies&a=createAttachment   |      |          


@contacts @actions
Scenario Outline: Contacts module actions
  Given I am logged in with <accessLevel> access level
  
  When I do <type> request on url "<url>"
  Then I should <bool> have permission
  
 Examples:
  | accessLevel | type    | url                                             | bool  |
  | DISABLED    | GET     | index.php?m=contacts&a=show                     | not   |
  | DISABLED    | GET     | index.php?m=contacts&a=add                      | not   |
  | DISABLED    | GET     | index.php?m=contacts&a=edit                     | not   |
  | DISABLED    | GET     | index.php?m=contacts&a=delete                   | not   |
  | DISABLED    | GET     | index.php?m=contacts&a=search                   | not   |        
  | DISABLED    | GET     | index.php?m=contacts&a=search                   | not   |
  | DISABLED    | GET     | index.php?m=contacts&a=listByView               | not   |
  | DISABLED    | GET     | index.php?m=contacts&a=addActivityScheduleEvent | not   |
  | DISABLED    | GET     | index.php?m=contacts&a=showColdCallList         | not   |
  | DISABLED    | GET     | index.php?m=contacts&a=downloadVCard            | not   |
  | DISABLED    | POST    | index.php?m=contacts&a=add                      | not   |                                                   
  | DISABLED    | POST    | index.php?m=contacts&a=edit                     | not   |                                                 
  | DISABLED    | POST    | index.php?m=contacts&a=addActivityScheduleEvent | not   |    
  | READONLY    | GET     | index.php?m=contacts&a=show                     |       |
  | READONLY    | GET     | index.php?m=contacts&a=add                      | not   |
  | READONLY    | GET     | index.php?m=contacts&a=edit                     | not   |
  | READONLY    | GET     | index.php?m=contacts&a=delete                   | not   |
  | READONLY    | GET     | index.php?m=contacts&a=search                   |       |
  | READONLY    | GET     | index.php?m=contacts&a=search                   |       |
  | READONLY    | GET     | index.php?m=contacts&a=listByView               |       |
  | READONLY    | GET     | index.php?m=contacts&a=addActivityScheduleEvent | not   |
  | READONLY    | GET     | index.php?m=contacts&a=showColdCallList         |       |
  | READONLY    | GET     | index.php?m=contacts&a=downloadVCard            |       |
  | READONLY    | POST    | index.php?m=contacts&a=add                      | not   |                                                                        
  | READONLY    | POST    | index.php?m=contacts&a=edit                     | not   |                                                                      
  | READONLY    | POST    | index.php?m=contacts&a=addActivityScheduleEvent | not   |    
  | EDIT        | GET     | index.php?m=contacts&a=show                     |       |
  | EDIT        | GET     | index.php?m=contacts&a=add                      |       |
  | EDIT        | GET     | index.php?m=contacts&a=edit                     |       |
  | EDIT        | GET     | index.php?m=contacts&a=delete                   | not   |
  | EDIT        | GET     | index.php?m=contacts&a=search                   |       |
  | EDIT        | GET     | index.php?m=contacts&a=search                   |       |
  | EDIT        | GET     | index.php?m=contacts&a=listByView               |       |
  | EDIT        | GET     | index.php?m=contacts&a=addActivityScheduleEvent |       |
  | EDIT        | GET     | index.php?m=contacts&a=showColdCallList         |       |
  | EDIT        | GET     | index.php?m=contacts&a=downloadVCard            |       |
  | EDIT        | POST    | index.php?m=contacts&a=add                      |       |                                                                        
  | EDIT        | POST    | index.php?m=contacts&a=edit                     |       |                                                                      
  | EDIT        | POST    | index.php?m=contacts&a=addActivityScheduleEvent |       |    
  | DELETE      | GET     | index.php?m=contacts&a=show                     |       |
  | DELETE      | GET     | index.php?m=contacts&a=add                      |       |
  | DELETE      | GET     | index.php?m=contacts&a=edit                     |       |
  | DELETE      | GET     | index.php?m=contacts&a=delete                   |       |
  | DELETE      | GET     | index.php?m=contacts&a=search                   |       |
  | DELETE      | GET     | index.php?m=contacts&a=search                   |       |
  | DELETE      | GET     | index.php?m=contacts&a=listByView               |       |
  | DELETE      | GET     | index.php?m=contacts&a=addActivityScheduleEvent |       |
  | DELETE      | GET     | index.php?m=contacts&a=showColdCallList         |       |
  | DELETE      | GET     | index.php?m=contacts&a=downloadVCard            |       |
  | DELETE      | POST    | index.php?m=contacts&a=add                      |       |                                                                        
  | DELETE      | POST    | index.php?m=contacts&a=edit                     |       |                                                                      
  | DELETE      | POST    | index.php?m=contacts&a=addActivityScheduleEvent |       |    
  | DEMO        | GET     | index.php?m=contacts&a=show                     |       |
  | DEMO        | GET     | index.php?m=contacts&a=add                      |       |
  | DEMO        | GET     | index.php?m=contacts&a=edit                     |       |
  | DEMO        | GET     | index.php?m=contacts&a=delete                   |       |
  | DEMO        | GET     | index.php?m=contacts&a=search                   |       |
  | DEMO        | GET     | index.php?m=contacts&a=search                   |       |
  | DEMO        | GET     | index.php?m=contacts&a=listByView               |       |
  | DEMO        | GET     | index.php?m=contacts&a=addActivityScheduleEvent |       |
  | DEMO        | GET     | index.php?m=contacts&a=showColdCallList         |       |
  | DEMO        | GET     | index.php?m=contacts&a=downloadVCard            |       |
  | DEMO        | POST    | index.php?m=contacts&a=add                      |       |                                                                        
  | DEMO        | POST    | index.php?m=contacts&a=edit                     |       |                                                                      
  | DEMO        | POST    | index.php?m=contacts&a=addActivityScheduleEvent |       |    
  | ADMIN       | GET     | index.php?m=contacts&a=show                     |       |
  | ADMIN       | GET     | index.php?m=contacts&a=add                      |       |
  | ADMIN       | GET     | index.php?m=contacts&a=edit                     |       |
  | ADMIN       | GET     | index.php?m=contacts&a=delete                   |       |
  | ADMIN       | GET     | index.php?m=contacts&a=search                   |       |
  | ADMIN       | GET     | index.php?m=contacts&a=search                   |       |
  | ADMIN       | GET     | index.php?m=contacts&a=listByView               |       |
  | ADMIN       | GET     | index.php?m=contacts&a=addActivityScheduleEvent |       |
  | ADMIN       | GET     | index.php?m=contacts&a=showColdCallList         |       |
  | ADMIN       | GET     | index.php?m=contacts&a=downloadVCard            |       |
  | ADMIN       | POST    | index.php?m=contacts&a=add                      |       |                                                                        
  | ADMIN       | POST    | index.php?m=contacts&a=edit                     |       |                                                                      
  | ADMIN       | POST    | index.php?m=contacts&a=addActivityScheduleEvent |       |    
  | MULTI_ADMIN | GET     | index.php?m=contacts&a=show                     |       |
  | MULTI_ADMIN | GET     | index.php?m=contacts&a=add                      |       |
  | MULTI_ADMIN | GET     | index.php?m=contacts&a=edit                     |       |
  | MULTI_ADMIN | GET     | index.php?m=contacts&a=delete                   |       |
  | MULTI_ADMIN | GET     | index.php?m=contacts&a=search                   |       |
  | MULTI_ADMIN | GET     | index.php?m=contacts&a=search                   |       |
  | MULTI_ADMIN | GET     | index.php?m=contacts&a=listByView               |       |
  | MULTI_ADMIN | GET     | index.php?m=contacts&a=addActivityScheduleEvent |       |
  | MULTI_ADMIN | GET     | index.php?m=contacts&a=showColdCallList         |       |
  | MULTI_ADMIN | GET     | index.php?m=contacts&a=downloadVCard            |       |
  | MULTI_ADMIN | POST    | index.php?m=contacts&a=add                      |       |                                                                        
  | MULTI_ADMIN | POST    | index.php?m=contacts&a=edit                     |       |                                                                      
  | MULTI_ADMIN | POST    | index.php?m=contacts&a=addActivityScheduleEvent |       |    
  | ROOT        | GET     | index.php?m=contacts&a=show                     |       |
  | ROOT        | GET     | index.php?m=contacts&a=add                      |       |
  | ROOT        | GET     | index.php?m=contacts&a=edit                     |       |
  | ROOT        | GET     | index.php?m=contacts&a=delete                   |       |
  | ROOT        | GET     | index.php?m=contacts&a=search                   |       |
  | ROOT        | GET     | index.php?m=contacts&a=search                   |       |
  | ROOT        | GET     | index.php?m=contacts&a=listByView               |       |
  | ROOT        | GET     | index.php?m=contacts&a=addActivityScheduleEvent |       |
  | ROOT        | GET     | index.php?m=contacts&a=showColdCallList         |       |
  | ROOT        | GET     | index.php?m=contacts&a=downloadVCard            |       |
  | ROOT        | POST    | index.php?m=contacts&a=add                      |       |                                                                        
  | ROOT        | POST    | index.php?m=contacts&a=edit                     |       |                                                                      
  | ROOT        | POST    | index.php?m=contacts&a=addActivityScheduleEvent |       |    
  
@activities @actions
Scenario Outline: Activity module actions
  Given I am logged in with <accessLevel> access level
  
  When I do <type> request on url "<url>"
  Then I should <bool> have permission 
  
  Examples:
  | accessLevel | type | url                                        | bool |
  | DISABLED    | GET  | index.php?m=activity&a=viewByDate          | not  |
  | DISABLED    | GET  | index.php?m=activity&a=listByViewDataGrid  | not  |
  | DISABLED    | POST | index.php?m=activity&a=viewByDate          | not  |
  | READONLY    | GET  | index.php?m=activity&a=viewByDate          |      |
  | READONLY    | GET  | index.php?m=activity&a=listByViewDataGrid  |      |  
  | READONLY    | POST | index.php?m=activity&a=viewByDate          |      |
  | EDIT        | GET  | index.php?m=activity&a=viewByDate          |      |
  | EDIT        | GET  | index.php?m=activity&a=listByViewDataGrid  |      |  
  | EDIT        | POST | index.php?m=activity&a=viewByDate          |      |
  | DELETE      | GET  | index.php?m=activity&a=viewByDate          |      |
  | DELETE      | GET  | index.php?m=activity&a=listByViewDataGrid  |      |  
  | DELETE      | POST | index.php?m=activity&a=viewByDate          |      |
  | DEMO        | GET  | index.php?m=activity&a=viewByDate          |      |
  | DEMO        | GET  | index.php?m=activity&a=listByViewDataGrid  |      |  
  | DEMO        | POST | index.php?m=activity&a=viewByDate          |      |
  | ADMIN       | GET  | index.php?m=activity&a=viewByDate          |      |
  | ADMIN       | GET  | index.php?m=activity&a=listByViewDataGrid  |      |  
  | ADMIN       | POST | index.php?m=activity&a=viewByDate          |      |
  | MULTI_ADMIN | GET  | index.php?m=activity&a=viewByDate          |      |
  | MULTI_ADMIN | GET  | index.php?m=activity&a=listByViewDataGrid  |      |  
  | MULTI_ADMIN | POST | index.php?m=activity&a=viewByDate          |      |
  | ROOT        | GET  | index.php?m=activity&a=viewByDate          |      |
  | ROOT        | GET  | index.php?m=activity&a=listByViewDataGrid  |      |  
  | ROOT        | POST | index.php?m=activity&a=viewByDate          |      |
  
@dashboard @home @actions
Scenario Outline: Home module actions
  Given I am logged in with <accessLevel> access level
  
   When I do <type> request on url "<url>"
   Then I should <bool> have permission 
  
 Examples:
  | accessLevel | type | url                                    | bool | 
  | DISABLED    | GET  | index.php?m=home&a=quickSearch         | not  |
  | DISABLED    | GET  | index.php?m=home&a=deleteSavedSearch   | not  |
  | DISABLED    | GET  | index.php?m=home&a=addSavedSearch      | not  |
  | DISABLED    | GET  | index.php?m=home&a=getAttachment       | not  |
  | DISABLED    | GET  | index.php?m=home&a=home                | not  |
  | READONLY    | GET  | index.php?m=home&a=quickSearch         |      |
  | READONLY    | GET  | index.php?m=home&a=deleteSavedSearch   |      |
  | READONLY    | GET  | index.php?m=home&a=addSavedSearch      |      |
  | READONLY    | GET  | index.php?m=home&a=getAttachment       |      |
  | READONLY    | GET  | index.php?m=home&a=home                |      |
  | EDIT        | GET  | index.php?m=home&a=quickSearch         |      |
  | EDIT        | GET  | index.php?m=home&a=deleteSavedSearch   |      |
  | EDIT        | GET  | index.php?m=home&a=addSavedSearch      |      |
  | EDIT        | GET  | index.php?m=home&a=getAttachment       |      |
  | EDIT        | GET  | index.php?m=home&a=home                |      |
  | DELETE      | GET  | index.php?m=home&a=quickSearch         |      |
  | DELETE      | GET  | index.php?m=home&a=deleteSavedSearch   |      |
  | DELETE      | GET  | index.php?m=home&a=addSavedSearch      |      |
  | DELETE      | GET  | index.php?m=home&a=getAttachment       |      |
  | DELETE      | GET  | index.php?m=home&a=home                |      |
  | DEMO        | GET  | index.php?m=home&a=quickSearch         |      |
  | DEMO        | GET  | index.php?m=home&a=deleteSavedSearch   |      |
  | DEMO        | GET  | index.php?m=home&a=addSavedSearch      |      |
  | DEMO        | GET  | index.php?m=home&a=getAttachment       |      |
  | DEMO        | GET  | index.php?m=home&a=home                |      |
  | ADMIN       | GET  | index.php?m=home&a=quickSearch         |      |
  | ADMIN       | GET  | index.php?m=home&a=deleteSavedSearch   |      |
  | ADMIN       | GET  | index.php?m=home&a=addSavedSearch      |      |
  | ADMIN       | GET  | index.php?m=home&a=getAttachment       |      |
  | ADMIN       | GET  | index.php?m=home&a=home                |      |
  | MULTI_ADMIN | GET  | index.php?m=home&a=quickSearch         |      |
  | MULTI_ADMIN | GET  | index.php?m=home&a=deleteSavedSearch   |      |
  | MULTI_ADMIN | GET  | index.php?m=home&a=addSavedSearch      |      |
  | MULTI_ADMIN | GET  | index.php?m=home&a=getAttachment       |      |
  | MULTI_ADMIN | GET  | index.php?m=home&a=home                |      |
  | ROOT        | GET  | index.php?m=home&a=quickSearch         |      |
  | ROOT        | GET  | index.php?m=home&a=deleteSavedSearch   |      |
  | ROOT        | GET  | index.php?m=home&a=addSavedSearch      |      |
  | ROOT        | GET  | index.php?m=home&a=getAttachment       |      |
  | ROOT        | GET  | index.php?m=home&a=home                |      |
  
@lists @actions
Scenario Outline: Lists module actions
  Given I am logged in with <accessLevel> access level
  
   When I do <type> request on url "<url>"
   Then I should <bool> have permission 
  
 Examples:
  | accessLevel | type | url                                            | bool | 
  | DISABLED    | GET  | index.php?m=lists&a=showList                   | not  |
  | DISABLED    | GET  | index.php?m=lists&a=quickActionAddToListModal  | not  |
  | DISABLED    | GET  | index.php?m=lists&a=addToListFromDatagridModal | not  |
  | DISABLED    | GET  | index.php?m=lists&a=removeFromListDatagrid     | not  |
  | DISABLED    | GET  | index.php?m=lists&a=deleteStaticList           | not  |
  | DISABLED    | GET  | index.php?m=lists&a=listByView                 | not  |
  | READONLY    | GET  | index.php?m=lists&a=showList                   |      |
  | READONLY    | GET  | index.php?m=lists&a=quickActionAddToListModal  |      |
  | READONLY    | GET  | index.php?m=lists&a=addToListFromDatagridModal |      |
  | READONLY    | GET  | index.php?m=lists&a=removeFromListDatagrid     |      |
  | READONLY    | GET  | index.php?m=lists&a=deleteStaticList           |      |
  | READONLY    | GET  | index.php?m=lists&a=listByView                 |      |
  | EDIT        | GET  | index.php?m=lists&a=showList                   |      |
  | EDIT        | GET  | index.php?m=lists&a=quickActionAddToListModal  |      |
  | EDIT        | GET  | index.php?m=lists&a=addToListFromDatagridModal |      |
  | EDIT        | GET  | index.php?m=lists&a=removeFromListDatagrid     |      |
  | EDIT        | GET  | index.php?m=lists&a=deleteStaticList           |      |
  | EDIT        | GET  | index.php?m=lists&a=listByView                 |      |
  | DELETE      | GET  | index.php?m=lists&a=showList                   |      |
  | DELETE      | GET  | index.php?m=lists&a=quickActionAddToListModal  |      |
  | DELETE      | GET  | index.php?m=lists&a=addToListFromDatagridModal |      |
  | DELETE      | GET  | index.php?m=lists&a=removeFromListDatagrid     |      |
  | DELETE      | GET  | index.php?m=lists&a=deleteStaticList           |      |
  | DELETE      | GET  | index.php?m=lists&a=listByView                 |      |
  | DEMO        | GET  | index.php?m=lists&a=showList                   |      |
  | DEMO        | GET  | index.php?m=lists&a=quickActionAddToListModal  |      |
  | DEMO        | GET  | index.php?m=lists&a=addToListFromDatagridModal |      |
  | DEMO        | GET  | index.php?m=lists&a=removeFromListDatagrid     |      |
  | DEMO        | GET  | index.php?m=lists&a=deleteStaticList           |      |
  | DEMO        | GET  | index.php?m=lists&a=listByView                 |      |
  | ADMIN       | GET  | index.php?m=lists&a=showList                   |      |
  | ADMIN       | GET  | index.php?m=lists&a=quickActionAddToListModal  |      |
  | ADMIN       | GET  | index.php?m=lists&a=addToListFromDatagridModal |      |
  | ADMIN       | GET  | index.php?m=lists&a=removeFromListDatagrid     |      |
  | ADMIN       | GET  | index.php?m=lists&a=deleteStaticList           |      |
  | ADMIN       | GET  | index.php?m=lists&a=listByView                 |      |
  | MULTI_ADMIN | GET  | index.php?m=lists&a=showList                   |      |
  | MULTI_ADMIN | GET  | index.php?m=lists&a=quickActionAddToListModal  |      | 
  | MULTI_ADMIN | GET  | index.php?m=lists&a=addToListFromDatagridModal |      | 
  | MULTI_ADMIN | GET  | index.php?m=lists&a=removeFromListDatagrid     |      | 
  | MULTI_ADMIN | GET  | index.php?m=lists&a=deleteStaticList           |      | 
  | MULTI_ADMIN | GET  | index.php?m=lists&a=listByView                 |      | 
  | ROOT        | GET  | index.php?m=lists&a=showList                   |      |
  | ROOT        | GET  | index.php?m=lists&a=quickActionAddToListModal  |      |
  | ROOT        | GET  | index.php?m=lists&a=addToListFromDatagridModal |      |
  | ROOT        | GET  | index.php?m=lists&a=removeFromListDatagrid     |      |
  | ROOT        | GET  | index.php?m=lists&a=deleteStaticList           |      |
  | ROOT        | GET  | index.php?m=lists&a=listByView                 |      |
  
 
@calendar @actions
Scenario Outline: Calendar module actions
  Given I am logged in with <accessLevel> access level
  
   When I do <type> request on url "<url>"
   Then I should <bool> have permission 
  
 Examples:
  | accessLevel | type | url                                            | bool | 
  | DISABLED    | GET  | index.php?m=calendar&a=dynamicData             | not  |
  | DISABLED    | GET  | index.php?m=calendar&a=deleteEvent             | not  |
  | DISABLED    | GET  | index.php?m=calendar&a=showCalendar            | not  |
  | DISABLED    | POST | index.php?m=calendar&a=addEvent                | not  |
  | DISABLED    | POST | index.php?m=calendar&a=editEvent               | not  |
  | READONLY    | GET  | index.php?m=calendar&a=dynamicData             |      |
  | READONLY    | GET  | index.php?m=calendar&a=deleteEvent             | not  |
  | READONLY    | GET  | index.php?m=calendar&a=showCalendar            |      |
  | READONLY    | POST | index.php?m=calendar&a=addEvent                | not  |
  | READONLY    | POST | index.php?m=calendar&a=editEvent               | not  |
  | EDIT        | GET  | index.php?m=calendar&a=dynamicData             |      |
  | EDIT        | GET  | index.php?m=calendar&a=deleteEvent             | not  |
  | EDIT        | GET  | index.php?m=calendar&a=showCalendar            |      |
  | EDIT        | POST | index.php?m=calendar&a=addEvent                |      |
  | EDIT        | POST | index.php?m=calendar&a=editEvent               |      |
  | DELETE      | GET  | index.php?m=calendar&a=dynamicData             |      |
  | DELETE      | GET  | index.php?m=calendar&a=deleteEvent             |      | 
  | DELETE      | GET  | index.php?m=calendar&a=showCalendar            |      |
  | DELETE      | POST | index.php?m=calendar&a=addEvent                |      |
  | DELETE      | POST | index.php?m=calendar&a=editEvent               |      |
  | DEMO        | GET  | index.php?m=calendar&a=dynamicData             |      |
  | DEMO        | GET  | index.php?m=calendar&a=deleteEvent             |      |
  | DEMO        | GET  | index.php?m=calendar&a=showCalendar            |      |
  | DEMO        | POST | index.php?m=calendar&a=addEvent                |      |
  | DEMO        | POST | index.php?m=calendar&a=editEvent               |      |
  | ADMIN       | GET  | index.php?m=calendar&a=dynamicData             |      |
  | ADMIN       | GET  | index.php?m=calendar&a=deleteEvent             |      |
  | ADMIN       | GET  | index.php?m=calendar&a=showCalendar            |      |
  | ADMIN       | POST | index.php?m=calendar&a=addEvent                |      |
  | ADMIN       | POST | index.php?m=calendar&a=editEvent               |      |
  | MULTI_ADMIN | GET  | index.php?m=calendar&a=dynamicData             |      |
  | MULTI_ADMIN | GET  | index.php?m=calendar&a=deleteEvent             |      |
  | MULTI_ADMIN | GET  | index.php?m=calendar&a=showCalendar            |      |
  | MULTI_ADMIN | POST | index.php?m=calendar&a=addEvent                |      |
  | MULTI_ADMIN | POST | index.php?m=calendar&a=editEvent               |      |
  | ROOT        | GET  | index.php?m=calendar&a=dynamicData             |      |
  | ROOT        | GET  | index.php?m=calendar&a=deleteEvent             |      |
  | ROOT        | GET  | index.php?m=calendar&a=showCalendar            |      |
  | ROOT        | POST | index.php?m=calendar&a=addEvent                |      |
  | ROOT        | POST | index.php?m=calendar&a=editEvent               |      |
  
@reports @actions
Scenario Outline: Reports module actions
  Given I am logged in with <accessLevel> access level
  
   When I do <type> request on url "<url>"
   Then I should <bool> have permission 
  
 Examples:
  | accessLevel | type | url                                                | bool |
  | DISABLED    | GET  | index.php?m=reports&a=graphView                    | not  |  
  | DISABLED    | GET  | index.php?m=reports&a=generateJobOrderReportPDF    | not  |
  | DISABLED    | GET  | index.php?m=reports&a=showSubmissionReport         | not  |
  | DISABLED    | GET  | index.php?m=reports&a=showPlacementReport          | not  |
  | DISABLED    | GET  | index.php?m=reports&a=customizeJobOrderReport      | not  |
  | DISABLED    | GET  | index.php?m=reports&a=customizeEEOReport           | not  |
  | DISABLED    | GET  | index.php?m=reports&a=generateEEOReportPreview     | not  |
  | DISABLED    | GET  | index.php?m=reports&a=reports                      | not  |
  | READONLY    | GET  | index.php?m=reports&a=graphView                    |      |
  | READONLY    | GET  | index.php?m=reports&a=generateJobOrderReportPDF    |      |
  | READONLY    | GET  | index.php?m=reports&a=showSubmissionReport         |      |
  | READONLY    | GET  | index.php?m=reports&a=showPlacementReport          |      |
  | READONLY    | GET  | index.php?m=reports&a=customizeJobOrderReport      |      |
  | READONLY    | GET  | index.php?m=reports&a=customizeEEOReport           |      |
  | READONLY    | GET  | index.php?m=reports&a=generateEEOReportPreview     |      |
  | READONLY    | GET  | index.php?m=reports&a=reports                      |      |
  | EDIT        | GET  | index.php?m=reports&a=graphView                    |      |
  | EDIT        | GET  | index.php?m=reports&a=generateJobOrderReportPDF    |      |
  | EDIT        | GET  | index.php?m=reports&a=showSubmissionReport         |      |
  | EDIT        | GET  | index.php?m=reports&a=showPlacementReport          |      |
  | EDIT        | GET  | index.php?m=reports&a=customizeJobOrderReport      |      |
  | EDIT        | GET  | index.php?m=reports&a=customizeEEOReport           |      |
  | EDIT        | GET  | index.php?m=reports&a=generateEEOReportPreview     |      |
  | EDIT        | GET  | index.php?m=reports&a=reports                      |      |
  | DELETE      | GET  | index.php?m=reports&a=graphView                    |      |
  | DELETE      | GET  | index.php?m=reports&a=generateJobOrderReportPDF    |      |
  | DELETE      | GET  | index.php?m=reports&a=showSubmissionReport         |      |
  | DELETE      | GET  | index.php?m=reports&a=showPlacementReport          |      |
  | DELETE      | GET  | index.php?m=reports&a=customizeJobOrderReport      |      |
  | DELETE      | GET  | index.php?m=reports&a=customizeEEOReport           |      |
  | DELETE      | GET  | index.php?m=reports&a=generateEEOReportPreview     |      |
  | DELETE      | GET  | index.php?m=reports&a=reports                      |      |
  | DEMO        | GET  | index.php?m=reports&a=graphView                    |      |
  | DEMO        | GET  | index.php?m=reports&a=generateJobOrderReportPDF    |      |
  | DEMO        | GET  | index.php?m=reports&a=showSubmissionReport         |      |
  | DEMO        | GET  | index.php?m=reports&a=showPlacementReport          |      |
  | DEMO        | GET  | index.php?m=reports&a=customizeJobOrderReport      |      |
  | DEMO        | GET  | index.php?m=reports&a=customizeEEOReport           |      |
  | DEMO        | GET  | index.php?m=reports&a=generateEEOReportPreview     |      |
  | DEMO        | GET  | index.php?m=reports&a=reports                      |      |
  | ADMIN       | GET  | index.php?m=reports&a=graphView                    |      |
  | ADMIN       | GET  | index.php?m=reports&a=generateJobOrderReportPDF    |      |
  | ADMIN       | GET  | index.php?m=reports&a=showSubmissionReport         |      |
  | ADMIN       | GET  | index.php?m=reports&a=showPlacementReport          |      |
  | ADMIN       | GET  | index.php?m=reports&a=customizeJobOrderReport      |      |
  | ADMIN       | GET  | index.php?m=reports&a=customizeEEOReport           |      |
  | ADMIN       | GET  | index.php?m=reports&a=generateEEOReportPreview     |      |
  | ADMIN       | GET  | index.php?m=reports&a=reports                      |      |
  | MULTI_ADMIN | GET  | index.php?m=reports&a=graphView                    |      |
  | MULTI_ADMIN | GET  | index.php?m=reports&a=generateJobOrderReportPDF    |      |
  | MULTI_ADMIN | GET  | index.php?m=reports&a=showSubmissionReport         |      |
  | MULTI_ADMIN | GET  | index.php?m=reports&a=showPlacementReport          |      |
  | MULTI_ADMIN | GET  | index.php?m=reports&a=customizeJobOrderReport      |      |
  | MULTI_ADMIN | GET  | index.php?m=reports&a=customizeEEOReport           |      |
  | MULTI_ADMIN | GET  | index.php?m=reports&a=generateEEOReportPreview     |      |
  | MULTI_ADMIN | GET  | index.php?m=reports&a=reports                      |      |
  | ROOT        | GET  | index.php?m=reports&a=graphView                    |      |
  | ROOT        | GET  | index.php?m=reports&a=generateJobOrderReportPDF    |      |
  | ROOT        | GET  | index.php?m=reports&a=showSubmissionReport         |      |
  | ROOT        | GET  | index.php?m=reports&a=showPlacementReport          |      |
  | ROOT        | GET  | index.php?m=reports&a=customizeJobOrderReport      |      |
  | ROOT        | GET  | index.php?m=reports&a=customizeEEOReport           |      |
  | ROOT        | GET  | index.php?m=reports&a=generateEEOReportPreview     |      |
  | ROOT        | GET  | index.php?m=reports&a=reports                      |      |
  
 @settings @actions
  Scenario Outline: Settings module actions
  Given I am logged in with <accessLevel> access level
  
   When I do <type> request on url "<url>"
   Then I should <bool> have permission 
  
  ####commented lines in table have URLs that are not called from anywhere in the code anymore
   Examples:
  | accessLevel | type | url                                        | bool | 
  | DISABLED    | GET  | index.php?m=settings&a=tags                | not  |
  | DISABLED    | POST | index.php?m=settings&a=changePassword      | not  |   
  | DISABLED    | POST | index.php?m=settings&a=manageUsers         | not  |
  | DISABLED    | POST | index.php?m=settings&a=professional        | not  |
  | DISABLED    | POST | index.php?m=settings&a=previewPage         | not  |
  | DISABLED    | POST | index.php?m=settings&a=previewPageTop      | not  |
  | DISABLED    | POST | index.php?m=settings&a=showUser            | not  |
  | DISABLED    | GET  | index.php?m=settings&a=addUser             | not  |
  | DISABLED    | POST | index.php?m=settings&a=addUser             | not  |
  | DISABLED    | GET  | index.php?m=settings&a=editUser            | not  |
  | DISABLED    | POST | index.php?m=settings&a=editUser            | not  | 
  | DISABLED    | GET  | index.php?m=settings&a=createBackup        | not  |
  | DISABLED    | GET  | index.php?m=settings&a=deleteBackup        | not  |
  | DISABLED    | GET  | index.php?m=settings&a=customizeExtraFields| not  |
  | DISABLED    | POST | index.php?m=settings&a=customizeExtraFields| not  |
  | DISABLED    | GET  | index.php?m=settings&a=customizeCalendar   | not  |
  | DISABLED    | POST | index.php?m=settings&a=customizeCalendar   | not  |
  | DISABLED    | GET  | index.php?m=settings&a=reports             | not  |
  | DISABLED    | GET  | index.php?m=settings&a=emailSettings       | not  |
  | DISABLED    | POST | index.php?m=settings&a=emailSettings       | not  |
  | DISABLED    | GET  | index.php?m=settings&a=careerPortalQuestionnairePreview| not  |
  | DISABLED    | GET  | index.php?m=settings&a=careerPortalQuestionnaire       | not  |
  | DISABLED    | POST | index.php?m=settings&a=careerPortalQuestionnaire       | not  |
  | DISABLED    | GET  | index.php?m=settings&a=careerPortalQuestionnaireUpdate | not  |
  | DISABLED    | GET  | index.php?m=settings&a=careerPortalTemplateEdit        | not  |
  | DISABLED    | POST | index.php?m=settings&a=careerPortalTemplateEdit        | not  |
  | DISABLED    | GET  | index.php?m=settings&a=careerPortalSettings| not  |
  | DISABLED    | POST | index.php?m=settings&a=careerPortalSettings| not  |
  | DISABLED    | GET  | index.php?m=settings&a=eeo                 | not  |
  | DISABLED    | POST | index.php?m=settings&a=eeo                 | not  |
  | DISABLED    | GET  | index.php?m=settings&a=onCareerPortalTweak | not  |
  | DISABLED    | GET  | index.php?m=settings&a=deleteUser          | not  |
  | DISABLED    | GET  | index.php?m=settings&a=emailTemplates      | not  |
  | DISABLED    | POST | index.php?m=settings&a=emailTemplates      | not  |
  | DISABLED    | POST | index.php?m=settings&a=aspLocalization     | not  |
  | DISABLED    | GET  | index.php?m=settings&a=loginActivity       | not  |
  | DISABLED    | GET  | index.php?m=settings&a=viewItemHistory     | not  |
  | DISABLED    | GET  | index.php?m=settings&a=getFirefoxModal     | not  |
  | DISABLED    | GET  | index.php?m=settings&a=administration      | not  |
  | DISABLED    | POST | index.php?m=settings&a=administration      | not  |
  | DISABLED    | GET  | index.php?m=settings&a=myProfile           | not  |
  | READONLY    | GET  | index.php?m=settings&a=tags                | not  |
  | READONLY    | POST | index.php?m=settings&a=changePassword      |      |
  | READONLY    | POST | index.php?m=settings&a=manageUsers         | not  |
  | READONLY    | POST | index.php?m=settings&a=professional        | not  |
  | READONLY    | POST | index.php?m=settings&a=previewPage         |      |
  | READONLY    | POST | index.php?m=settings&a=previewPageTop      |      |
  | READONLY    | POST | index.php?m=settings&a=showUser            | not  |
  | READONLY    | GET  | index.php?m=settings&a=addUser             | not  |
  | READONLY    | POST | index.php?m=settings&a=addUser             | not  |
  | READONLY    | GET  | index.php?m=settings&a=editUser            | not  |
  | READONLY    | POST | index.php?m=settings&a=editUser            | not  |
  | READONLY    | GET  | index.php?m=settings&a=createBackup        | not  |
  | READONLY    | GET  | index.php?m=settings&a=deleteBackup        | not  |
  | READONLY    | GET  | index.php?m=settings&a=customizeExtraFields| not  |
  | READONLY    | POST | index.php?m=settings&a=customizeExtraFields| not  |
  | READONLY    | GET  | index.php?m=settings&a=customizeCalendar   | not  |
  | READONLY    | POST | index.php?m=settings&a=customizeCalendar   | not  |
  | READONLY    | GET  | index.php?m=settings&a=reports             | not  |
  | READONLY    | GET  | index.php?m=settings&a=emailSettings       | not  |
  | READONLY    | POST | index.php?m=settings&a=emailSettings       | not  |
  | READONLY    | GET  | index.php?m=settings&a=careerPortalQuestionnairePreview| not  |
  | READONLY    | GET  | index.php?m=settings&a=careerPortalQuestionnaire       | not  |
  | READONLY    | POST | index.php?m=settings&a=careerPortalQuestionnaire       | not  |
  | READONLY    | GET  | index.php?m=settings&a=careerPortalQuestionnaireUpdate | not  |
  | READONLY    | GET  | index.php?m=settings&a=careerPortalTemplateEdit        | not  |
  | READONLY    | POST | index.php?m=settings&a=careerPortalTemplateEdit        | not  |
  | READONLY    | GET  | index.php?m=settings&a=careerPortalSettings| not  |
  | READONLY    | POST | index.php?m=settings&a=careerPortalSettings| not  |
  | READONLY    | GET  | index.php?m=settings&a=eeo                 | not  |
  | READONLY    | POST | index.php?m=settings&a=eeo                 | not  |
  | READONLY    | GET  | index.php?m=settings&a=onCareerPortalTweak | not  |
  | READONLY    | GET  | index.php?m=settings&a=deleteUser          | not  |
  | READONLY    | GET  | index.php?m=settings&a=emailTemplates      | not  |
  | READONLY    | POST | index.php?m=settings&a=emailTemplates      | not  |
  | READONLY    | POST | index.php?m=settings&a=aspLocalization     | not  |
  | READONLY    | GET  | index.php?m=settings&a=loginActivity       | not  |
  | READONLY    | GET  | index.php?m=settings&a=viewItemHistory     | not  |
  | READONLY    | GET  | index.php?m=settings&a=getFirefoxModal     |      |
  | READONLY    | GET  | index.php?m=settings&a=administration      | not  |
  | READONLY    | POST | index.php?m=settings&a=administration      | not  |
  | READONLY    | GET  | index.php?m=settings&a=myProfile           |      |
  | EDIT        | GET  | index.php?m=settings&a=tags                | not  |
  | EDIT        | POST | index.php?m=settings&a=changePassword      |      |
  | EDIT        | POST | index.php?m=settings&a=manageUsers         | not  |
  | EDIT        | POST | index.php?m=settings&a=professional        | not  |
  | EDIT        | POST | index.php?m=settings&a=previewPage         |      |
  | EDIT        | POST | index.php?m=settings&a=previewPageTop      |      |
  | EDIT        | POST | index.php?m=settings&a=showUser            | not  |
  | EDIT        | GET  | index.php?m=settings&a=addUser             | not  |
  | EDIT        | POST | index.php?m=settings&a=addUser             | not  |
  | EDIT        | GET  | index.php?m=settings&a=editUser            | not  |
  | EDIT        | POST | index.php?m=settings&a=editUser            | not  |
  | EDIT        | GET  | index.php?m=settings&a=createBackup        | not  |
  | EDIT        | GET  | index.php?m=settings&a=deleteBackup        | not  |
  | EDIT        | GET  | index.php?m=settings&a=customizeExtraFields| not  |
  | EDIT        | POST | index.php?m=settings&a=customizeExtraFields| not  |
  | EDIT        | GET  | index.php?m=settings&a=customizeCalendar   | not  |
  | EDIT        | POST | index.php?m=settings&a=customizeCalendar   | not  |
  | EDIT        | GET  | index.php?m=settings&a=reports             | not  |
  | EDIT        | GET  | index.php?m=settings&a=emailSettings       | not  |
  | EDIT        | POST | index.php?m=settings&a=emailSettings       | not  |
  | EDIT        | GET  | index.php?m=settings&a=careerPortalQuestionnairePreview| not  |
  | EDIT        | GET  | index.php?m=settings&a=careerPortalQuestionnaire       | not  |
  | EDIT        | POST | index.php?m=settings&a=careerPortalQuestionnaire       | not  |
  | EDIT        | GET  | index.php?m=settings&a=careerPortalQuestionnaireUpdate | not  |
  | EDIT        | GET  | index.php?m=settings&a=careerPortalTemplateEdit        | not  |
  | EDIT        | POST | index.php?m=settings&a=careerPortalTemplateEdit        | not  |
  | EDIT        | GET  | index.php?m=settings&a=careerPortalSettings| not  |
  | EDIT        | POST | index.php?m=settings&a=careerPortalSettings| not  |
  | EDIT        | GET  | index.php?m=settings&a=eeo                 | not  |
  | EDIT        | POST | index.php?m=settings&a=eeo                 | not  |
  | EDIT        | GET  | index.php?m=settings&a=onCareerPortalTweak | not  |
  | EDIT        | GET  | index.php?m=settings&a=deleteUser          | not  |
  | EDIT        | GET  | index.php?m=settings&a=emailTemplates      | not  |
  | EDIT        | POST | index.php?m=settings&a=emailTemplates      | not  |
  | EDIT        | POST | index.php?m=settings&a=aspLocalization     | not  |
  | EDIT        | GET  | index.php?m=settings&a=loginActivity       | not  |
  | EDIT        | GET  | index.php?m=settings&a=viewItemHistory     | not  |
  | EDIT        | GET  | index.php?m=settings&a=getFirefoxModal     |      |
  | EDIT        | GET  | index.php?m=settings&a=administration      | not  |
  | EDIT        | POST | index.php?m=settings&a=administration      | not  |
  | EDIT        | GET  | index.php?m=settings&a=myProfile           |      |
  | DELETE      | GET  | index.php?m=settings&a=tags                | not  |
  | DELETE      | POST | index.php?m=settings&a=changePassword      |      |
  | DELETE      | POST | index.php?m=settings&a=manageUsers         | not  |
  | DELETE      | POST | index.php?m=settings&a=professional        | not  |
  | DELETE      | POST | index.php?m=settings&a=previewPage         |      |
  | DELETE      | POST | index.php?m=settings&a=previewPageTop      |      |
  | DELETE      | POST | index.php?m=settings&a=showUser            | not  |
  | DELETE      | GET  | index.php?m=settings&a=addUser             | not  |
  | DELETE      | POST | index.php?m=settings&a=addUser             | not  |
  | DELETE      | GET  | index.php?m=settings&a=editUser            | not  |
  | DELETE      | POST | index.php?m=settings&a=editUser            | not  |
  | DELETE      | GET  | index.php?m=settings&a=createBackup        | not  |
  | DELETE      | GET  | index.php?m=settings&a=deleteBackup        | not  |
  | DELETE      | GET  | index.php?m=settings&a=customizeExtraFields| not  |
  | DELETE      | POST | index.php?m=settings&a=customizeExtraFields| not  |
  | DELETE      | GET  | index.php?m=settings&a=customizeCalendar   | not  |
  | DELETE      | POST | index.php?m=settings&a=customizeCalendar   | not  |
  | DELETE      | GET  | index.php?m=settings&a=reports             | not  |
  | DELETE      | GET  | index.php?m=settings&a=emailSettings       | not  |
  | DELETE      | POST | index.php?m=settings&a=emailSettings       | not  |
  | DELETE      | GET  | index.php?m=settings&a=careerPortalQuestionnairePreview| not  |
  | DELETE      | GET  | index.php?m=settings&a=careerPortalQuestionnaire       | not  |
  | DELETE      | POST | index.php?m=settings&a=careerPortalQuestionnaire       | not  |
  | DELETE      | GET  | index.php?m=settings&a=careerPortalQuestionnaireUpdate | not  |
  | DELETE      | GET  | index.php?m=settings&a=careerPortalTemplateEdit        | not  |
  | DELETE      | POST | index.php?m=settings&a=careerPortalTemplateEdit        | not  |
  | DELETE      | GET  | index.php?m=settings&a=careerPortalSettings| not  |
  | DELETE      | POST | index.php?m=settings&a=careerPortalSettings| not  |
  | DELETE      | GET  | index.php?m=settings&a=eeo                 | not  |
  | DELETE      | POST | index.php?m=settings&a=eeo                 | not  |
  | DELETE      | GET  | index.php?m=settings&a=onCareerPortalTweak | not  |
  | DELETE      | GET  | index.php?m=settings&a=deleteUser          | not  |
  | DELETE      | GET  | index.php?m=settings&a=emailTemplates      | not  |
  | DELETE      | POST | index.php?m=settings&a=emailTemplates      | not  |
  | DELETE      | POST | index.php?m=settings&a=aspLocalization     | not  |
  | DELETE      | GET  | index.php?m=settings&a=loginActivity       | not  |
  | DELETE      | GET  | index.php?m=settings&a=viewItemHistory     | not  |
  | DELETE      | GET  | index.php?m=settings&a=getFirefoxModal     |      |
  | DELETE      | GET  | index.php?m=settings&a=administration      | not  |
  | DELETE      | POST | index.php?m=settings&a=administration      | not  |
  | DELETE      | GET  | index.php?m=settings&a=myProfile           |      |
  | DEMO        | GET  | index.php?m=settings&a=tags                | not  |
  | DEMO        | POST | index.php?m=settings&a=changePassword      | not  |
  | DEMO        | POST | index.php?m=settings&a=manageUsers         |      |
  | DEMO        | POST | index.php?m=settings&a=professional        |      |
  | DEMO        | POST | index.php?m=settings&a=previewPage         |      |
  | DEMO        | POST | index.php?m=settings&a=previewPageTop      |      |
  | DEMO        | POST | index.php?m=settings&a=showUser            |      |
  | DEMO        | GET  | index.php?m=settings&a=addUser             |      |
  | DEMO        | POST | index.php?m=settings&a=addUser             | not  |
  | DEMO        | GET  | index.php?m=settings&a=editUser            |      |
  | DEMO        | POST | index.php?m=settings&a=editUser            | not  |
  | DEMO        | GET  | index.php?m=settings&a=createBackup        | not  |
  | DEMO        | GET  | index.php?m=settings&a=deleteBackup        | not  |
  | DEMO        | GET  | index.php?m=settings&a=customizeExtraFields|      |
  | DEMO        | POST | index.php?m=settings&a=customizeExtraFields| not  |
  | DEMO        | GET  | index.php?m=settings&a=customizeCalendar   |      |
  | DEMO        | POST | index.php?m=settings&a=customizeCalendar   | not  |
  | DEMO        | GET  | index.php?m=settings&a=reports             |      |
  | DEMO        | GET  | index.php?m=settings&a=emailSettings       |      |
  | DEMO        | POST | index.php?m=settings&a=emailSettings       | not  |
  | DEMO        | GET  | index.php?m=settings&a=careerPortalQuestionnairePreview|      |
  | DEMO        | GET  | index.php?m=settings&a=careerPortalQuestionnaire       |      |
  | DEMO        | POST | index.php?m=settings&a=careerPortalQuestionnaire       |      |
  | DEMO        | GET  | index.php?m=settings&a=careerPortalQuestionnaireUpdate |      |
  | DEMO        | GET  | index.php?m=settings&a=careerPortalTemplateEdit        |      |
  | DEMO        | POST | index.php?m=settings&a=careerPortalTemplateEdit        | not  |
  | DEMO        | GET  | index.php?m=settings&a=careerPortalSettings|      |
  | DEMO        | POST | index.php?m=settings&a=careerPortalSettings| not  |
  | DEMO        | GET  | index.php?m=settings&a=eeo                 |      |
  | DEMO        | POST | index.php?m=settings&a=eeo                 | not  |
  | DEMO        | GET  | index.php?m=settings&a=onCareerPortalTweak | not  |
  | DEMO        | GET  | index.php?m=settings&a=deleteUser          | not  |
  | DEMO        | GET  | index.php?m=settings&a=emailTemplates      |      |
  | DEMO        | POST | index.php?m=settings&a=emailTemplates      | not  |
  | DEMO        | POST | index.php?m=settings&a=aspLocalization     | not  |
  | DEMO        | GET  | index.php?m=settings&a=loginActivity       |      |
  | DEMO        | GET  | index.php?m=settings&a=viewItemHistory     |      |
  | DEMO        | GET  | index.php?m=settings&a=getFirefoxModal     |      |
  | DEMO        | GET  | index.php?m=settings&a=administration      |      |
  | DEMO        | POST | index.php?m=settings&a=administration      | not  |
  | DEMO        | GET  | index.php?m=settings&a=myProfile           |      |
  | ADMIN       | GET  | index.php?m=settings&a=tags                |      |
  | ADMIN       | POST | index.php?m=settings&a=changePassword      |      |
  | ADMIN       | POST | index.php?m=settings&a=manageUsers         |      |
  | ADMIN       | POST | index.php?m=settings&a=professional        |      |
  | ADMIN       | POST | index.php?m=settings&a=previewPage         |      |
  | ADMIN       | POST | index.php?m=settings&a=previewPageTop      |      |
  | ADMIN       | POST | index.php?m=settings&a=showUser            |      |
  | ADMIN       | GET  | index.php?m=settings&a=addUser             |      |
  | ADMIN       | POST | index.php?m=settings&a=addUser             |      |
  | ADMIN       | GET  | index.php?m=settings&a=editUser            |      |
  | ADMIN       | POST | index.php?m=settings&a=editUser            |      |
  | ADMIN       | GET  | index.php?m=settings&a=createBackup        |      |
  | ADMIN       | GET  | index.php?m=settings&a=deleteBackup        |      |
  | ADMIN       | GET  | index.php?m=settings&a=customizeExtraFields|      |
  | ADMIN       | POST | index.php?m=settings&a=customizeExtraFields|      |
  | ADMIN       | GET  | index.php?m=settings&a=customizeCalendar   |      |
  | ADMIN       | POST | index.php?m=settings&a=customizeCalendar   |      |
  | ADMIN       | GET  | index.php?m=settings&a=reports             |      |
  | ADMIN       | GET  | index.php?m=settings&a=emailSettings       |      |
  | ADMIN       | POST | index.php?m=settings&a=emailSettings       |      |
  | ADMIN       | GET  | index.php?m=settings&a=careerPortalQuestionnairePreview|      |
  | ADMIN       | GET  | index.php?m=settings&a=careerPortalQuestionnaire       |      |
  | ADMIN       | POST | index.php?m=settings&a=careerPortalQuestionnaire       |      |
  | ADMIN       | GET  | index.php?m=settings&a=careerPortalQuestionnaireUpdate |      |
  | ADMIN       | GET  | index.php?m=settings&a=careerPortalTemplateEdit        |      |
  | ADMIN       | POST | index.php?m=settings&a=careerPortalTemplateEdit        |      |
  | ADMIN       | GET  | index.php?m=settings&a=careerPortalSettings|      |
  | ADMIN       | POST | index.php?m=settings&a=careerPortalSettings|      |
  | ADMIN       | GET  | index.php?m=settings&a=eeo                 |      |
  | ADMIN       | POST | index.php?m=settings&a=eeo                 |      |
  | ADMIN       | GET  | index.php?m=settings&a=onCareerPortalTweak |      |
  | ADMIN       | GET  | index.php?m=settings&a=deleteUser          |      |
  | ADMIN       | GET  | index.php?m=settings&a=emailTemplates      |      |
  | ADMIN       | POST | index.php?m=settings&a=emailTemplates      |      |
  | ADMIN       | POST | index.php?m=settings&a=aspLocalization     |      |
  | ADMIN       | GET  | index.php?m=settings&a=loginActivity       |      |
  | ADMIN       | GET  | index.php?m=settings&a=viewItemHistory     |      |
  | ADMIN       | GET  | index.php?m=settings&a=getFirefoxModal     |      |
  | ADMIN       | GET  | index.php?m=settings&a=administration      |      |
  | ADMIN       | POST | index.php?m=settings&a=administration      |      |
  | ADMIN       | GET  | index.php?m=settings&a=myProfile           |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=tags                |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=changePassword      |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=manageUsers         |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=professional        |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=previewPage         |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=previewPageTop      |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=showUser            |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=addUser             |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=addUser             |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=editUser            |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=editUser            |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=createBackup        |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=deleteBackup        |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=customizeExtraFields|      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=customizeExtraFields|      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=customizeCalendar   |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=customizeCalendar   |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=reports             |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=emailSettings       |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=emailSettings       |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=careerPortalQuestionnairePreview|      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=careerPortalQuestionnaire       |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=careerPortalQuestionnaire       |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=careerPortalQuestionnaireUpdate |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=careerPortalTemplateEdit        |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=careerPortalTemplateEdit        |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=careerPortalSettings|      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=careerPortalSettings|      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=eeo                 |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=eeo                 |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=onCareerPortalTweak |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=deleteUser          |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=emailTemplates      |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=emailTemplates      |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=aspLocalization     |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=loginActivity       |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=viewItemHistory     |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=getFirefoxModal     |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=administration      |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=administration      |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=myProfile           |      |
  | ROOT        | GET  | index.php?m=settings&a=tags                |      |
  | ROOT        | POST | index.php?m=settings&a=changePassword      |      |
  | ROOT        | POST | index.php?m=settings&a=manageUsers         |      |
  | ROOT        | POST | index.php?m=settings&a=professional        |      |
  | ROOT        | POST | index.php?m=settings&a=previewPage         |      |
  | ROOT        | POST | index.php?m=settings&a=previewPageTop      |      |
  | ROOT        | POST | index.php?m=settings&a=showUser            |      |
  | ROOT        | GET  | index.php?m=settings&a=addUser             |      |
  | ROOT        | POST | index.php?m=settings&a=addUser             |      |
  | ROOT        | GET  | index.php?m=settings&a=editUser            |      |
  | ROOT        | POST | index.php?m=settings&a=editUser            |      |
  | ROOT        | GET  | index.php?m=settings&a=createBackup        |      |
  | ROOT        | GET  | index.php?m=settings&a=deleteBackup        |      |
  | ROOT        | GET  | index.php?m=settings&a=customizeExtraFields|      |
  | ROOT        | POST | index.php?m=settings&a=customizeExtraFields|      |
  | ROOT        | GET  | index.php?m=settings&a=customizeCalendar   |      |
  | ROOT        | POST | index.php?m=settings&a=customizeCalendar   |      |
  | ROOT        | GET  | index.php?m=settings&a=reports             |      |
  | ROOT        | GET  | index.php?m=settings&a=emailSettings       |      |
  | ROOT        | POST | index.php?m=settings&a=emailSettings       |      |
  | ROOT        | GET  | index.php?m=settings&a=careerPortalQuestionnairePreview|      |
  | ROOT        | GET  | index.php?m=settings&a=careerPortalQuestionnaire       |      |
  | ROOT        | POST | index.php?m=settings&a=careerPortalQuestionnaire       |      |
  | ROOT        | GET  | index.php?m=settings&a=careerPortalQuestionnaireUpdate |      |
  | ROOT        | GET  | index.php?m=settings&a=careerPortalTemplateEdit        |      |
  | ROOT        | POST | index.php?m=settings&a=careerPortalTemplateEdit        |      |
  | ROOT        | GET  | index.php?m=settings&a=careerPortalSettings|      |
  | ROOT        | POST | index.php?m=settings&a=careerPortalSettings|      |
  | ROOT        | GET  | index.php?m=settings&a=eeo                 |      |
  | ROOT        | POST | index.php?m=settings&a=eeo                 |      |
  | ROOT        | GET  | index.php?m=settings&a=onCareerPortalTweak |      |
  | ROOT        | GET  | index.php?m=settings&a=deleteUser          |      |
  | ROOT        | GET  | index.php?m=settings&a=emailTemplates      |      |
  | ROOT        | POST | index.php?m=settings&a=emailTemplates      |      |
  | ROOT        | POST | index.php?m=settings&a=aspLocalization     |      |
  | ROOT        | GET  | index.php?m=settings&a=loginActivity       |      |
  | ROOT        | GET  | index.php?m=settings&a=viewItemHistory     |      |
  | ROOT        | GET  | index.php?m=settings&a=getFirefoxModal     |      |
  | ROOT        | GET  | index.php?m=settings&a=administration      |      |
  | ROOT        | POST | index.php?m=settings&a=administration      |      |
  | ROOT        | GET  | index.php?m=settings&a=myProfile           |      |
  | DISABLED    | GET  | index.php?m=settings&a=newInstallPassword  | not  |
  | DISABLED    | POST | index.php?m=settings&a=newInstallPassword  | not  |
  | DISABLED    | GET  | index.php?m=settings&a=forceEmail          | not  |
  | DISABLED    | POST | index.php?m=settings&a=forceEmail          | not  |
  | DISABLED    | GET  | index.php?m=settings&a=newSiteName         | not  | 
  | DISABLED    | POST | index.php?m=settings&a=newSiteName         | not  |
  | DISABLED    | GET  | index.php?m=settings&a=upgradeSiteName     | not  |
  | DISABLED    | POST | index.php?m=settings&a=upgradeSiteName     | not  |
  | DISABLED    | GET  | index.php?m=settings&a=newInstallFinished  | not  |
  | DISABLED    | POST | index.php?m=settings&a=newInstallFinished  | not  |
  #| READONLY    | GET  | index.php?m=settings&a=newInstallPassword  | not  |
  #| READONLY    | POST | index.php?m=settings&a=newInstallPassword  | not  |
  #| READONLY    | GET  | index.php?m=settings&a=forceEmail          | not  |
  #| READONLY    | POST | index.php?m=settings&a=forceEmail          | not  |
  #| READONLY    | GET  | index.php?m=settings&a=newSiteName         | not  |
  #| READONLY    | POST | index.php?m=settings&a=newSiteName         | not  |
  #| READONLY    | GET  | index.php?m=settings&a=upgradeSiteName     | not  |
  #| READONLY    | POST | index.php?m=settings&a=upgradeSiteName     | not  |
  #| READONLY    | GET  | index.php?m=settings&a=newInstallFinished  | not  |
  #| READONLY    | POST | index.php?m=settings&a=newInstallFinished  | not  |
  #| EDIT        | GET  | index.php?m=settings&a=newInstallPassword  | not  |
  #| EDIT        | POST | index.php?m=settings&a=newInstallPassword  | not  |
  #| EDIT        | GET  | index.php?m=settings&a=forceEmail          | not  |
  #| EDIT        | POST | index.php?m=settings&a=forceEmail          | not  |
  #| EDIT        | GET  | index.php?m=settings&a=newSiteName         | not  |
  #| EDIT        | POST | index.php?m=settings&a=newSiteName         | not  |
  #| EDIT        | GET  | index.php?m=settings&a=upgradeSiteName     | not  |
  #| EDIT        | POST | index.php?m=settings&a=upgradeSiteName     | not  |
  #| EDIT        | GET  | index.php?m=settings&a=newInstallFinished  | not  |
  #| EDIT        | POST | index.php?m=settings&a=newInstallFinished  | not  |
  #| DELETE      | GET  | index.php?m=settings&a=newInstallPassword  | not  |
  #| DELETE      | POST | index.php?m=settings&a=newInstallPassword  | not  |
  #| DELETE      | GET  | index.php?m=settings&a=forceEmail          | not  |
  #| DELETE      | POST | index.php?m=settings&a=forceEmail          | not  |
  #| DELETE      | GET  | index.php?m=settings&a=newSiteName         | not  |
  #| DELETE      | POST | index.php?m=settings&a=newSiteName         | not  |
  #| DELETE      | GET  | index.php?m=settings&a=upgradeSiteName     | not  |
  #| DELETE      | POST | index.php?m=settings&a=upgradeSiteName     | not  |
  #| DELETE      | GET  | index.php?m=settings&a=newInstallFinished  | not  |
  #| DELETE      | POST | index.php?m=settings&a=newInstallFinished  | not  |
  #| DEMO        | GET  | index.php?m=settings&a=newInstallPassword  | not  |
  #| DEMO        | POST | index.php?m=settings&a=newInstallPassword  | not  |
  #| DEMO        | GET  | index.php?m=settings&a=forceEmail          | not  |
  #| DEMO        | POST | index.php?m=settings&a=forceEmail          | not  |
  #| DEMO        | GET  | index.php?m=settings&a=newSiteName         | not  |
  #| DEMO        | POST | index.php?m=settings&a=newSiteName         | not  |
  #| DEMO        | GET  | index.php?m=settings&a=upgradeSiteName     | not  |
  #| DEMO        | POST | index.php?m=settings&a=upgradeSiteName     | not  |
  #| DEMO        | GET  | index.php?m=settings&a=newInstallFinished  | not  |
  #| DEMO        | POST | index.php?m=settings&a=newInstallFinished  | not  |
  | ADMIN       | GET  | index.php?m=settings&a=newInstallPassword  |      |
  | ADMIN       | POST | index.php?m=settings&a=newInstallPassword  |      |
  | ADMIN       | GET  | index.php?m=settings&a=forceEmail          |      |
  | ADMIN       | POST | index.php?m=settings&a=forceEmail          |      |
  | ADMIN       | GET  | index.php?m=settings&a=newSiteName         |      |
  | ADMIN       | POST | index.php?m=settings&a=newSiteName         |      |
  | ADMIN       | GET  | index.php?m=settings&a=upgradeSiteName     |      |
  | ADMIN       | POST | index.php?m=settings&a=upgradeSiteName     |      |
  | ADMIN       | GET  | index.php?m=settings&a=newInstallFinished  |      |
  | ADMIN       | POST | index.php?m=settings&a=newInstallFinished  |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=newInstallPassword  |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=newInstallPassword  |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=forceEmail          |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=forceEmail          |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=newSiteName         |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=newSiteName         |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=upgradeSiteName     |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=upgradeSiteName     |      |
  | MULTI_ADMIN | GET  | index.php?m=settings&a=newInstallFinished  |      |
  | MULTI_ADMIN | POST | index.php?m=settings&a=newInstallFinished  |      |
  | ROOT        | GET  | index.php?m=settings&a=newInstallPassword  |      |
  | ROOT        | POST | index.php?m=settings&a=newInstallPassword  |      |
  | ROOT        | GET  | index.php?m=settings&a=forceEmail          |      |
  | ROOT        | POST | index.php?m=settings&a=forceEmail          |      |
  | ROOT        | GET  | index.php?m=settings&a=newSiteName         |      |
  | ROOT        | POST | index.php?m=settings&a=newSiteName         |      |
  | ROOT        | GET  | index.php?m=settings&a=upgradeSiteName     |      |
  | ROOT        | POST | index.php?m=settings&a=upgradeSiteName     |      |
  | ROOT        | GET  | index.php?m=settings&a=newInstallFinished  |      |
  | ROOT        | POST | index.php?m=settings&a=newInstallFinished  |      |
  ####commented lines in table have URLs that are not called from anywhere in the code anymore
  
  #### AJAX not tested 
  #When I do GET request "index.php?m=settings&a=ajax_tags_add"
  #And the response should <FAddTags> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_tags_del"
  #And the response should <FDelTags> contain "You don't have permission"
    
  #When I do GET request "index.php?m=settings&a=ajax_tags_upd"
  #And the response should <FUpdTags> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardAddUser"
  #And the response should <FAddUser> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardDeleteUser"
  #And the response should <FDeleteUser> contain "You don't have permission"
     
  #When I do GET request "index.php?m=settings&a=ajax_wizardCheckKey"
  #And the response should <FCheckKey> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardLocalization"
  #And the response should <FLocalization> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardFirstTimeSetup"
  #And the response should <FFirstTimeSetup> contain "You don't have permission"
    
  #When I do GET request "index.php?m=settings&a=ajax_wizardLicense"
  #And the response should <FLicense> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardPassword"
  #And the response should <FPasswd> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardSiteName"
  #And the response should <FSiteName> contain "You don't have permission"
    
  #When I do GET request "index.php?m=settings&a=ajax_wizardEmail"
  #And the response should <FEmail> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardImport"
  #And the response should <FImport> contain "You don't have permission"
  
  #When I do GET request "index.php?m=settings&a=ajax_wizardWebsite"
  #And the response should <FWebsite> contain "You don't have permission"
 