@security @sectors
Feature: Sector administration permissions
  Scenario Outline: Sector administration retains administrator-only access
    Given I am logged in with <accessLevel> access level
    When I do <type> request on url "index.php?m=settings&a=sectors"
    Then I should <denied> have permission
    Examples:
      | accessLevel | type | denied |
      | READONLY    | GET  | not    |
      | EDIT        | GET  | not    |
      | DELETE      | GET  | not    |
      | EDIT        | POST | not    |
      | ADMIN       | GET  |        |

  Scenario: Sector maintenance rejects invalid CSRF tokens
    Given I am logged in with ADMIN access level
    When I submit Sector maintenance with an invalid CSRF token
    Then the Sector request is rejected for an invalid CSRF token
