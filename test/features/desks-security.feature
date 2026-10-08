@security @desks
Feature: Desk administration permissions
  Scenario Outline: Desk administration retains administrator-only access
    Given I am logged in with <accessLevel> access level
    When I do <type> request on url "index.php?m=settings&a=desks"
    Then I should <denied> have permission
    Examples:
      | accessLevel | type | denied |
      | READONLY    | GET  | not    |
      | EDIT        | GET  | not    |
      | DELETE      | GET  | not    |
      | EDIT        | POST | not    |
      | ADMIN       | GET  |        |
