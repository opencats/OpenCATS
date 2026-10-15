@security @chart_permissions
Feature: Migrated charts retain containing-page permissions
  Scenario Outline: Graph families retain access checks
    Given I am logged in with <accessLevel> access level
    When I do GET request on url "<url>"
    Then I should <denied> have permission
    Examples:
      | accessLevel | url                                                                             | denied |
      | DISABLED    | index.php?m=home                                                                 | not    |
      | DISABLED    | index.php?m=home&a=hiringOverview&view=1                                         | not    |
      | READONLY    | index.php?m=home&a=hiringOverview&view=2                                         |        |
      | READONLY    | index.php?m=home                                                                 |        |
      | DISABLED    | index.php?m=joborders&a=show&jobOrderID=40001                                      | not    |
      | READONLY    | index.php?m=joborders&a=show&jobOrderID=40001                                      |        |
      | DISABLED    | index.php?m=reports&a=graphView&jobOrderID=40001                                   | not    |
      | READONLY    | index.php?m=reports&a=graphView&jobOrderID=40001                                   |        |
      | READONLY    | index.php?m=reports&a=generateEEOReportPreview&period=all&status=all               | not    |
      | ADMIN       | index.php?m=reports&a=generateEEOReportPreview&period=all&status=all               |        |
      | DISABLED    | index.php?m=reports&a=generateJobOrderReportPDF&dataSet=9,7,3,1                    | not    |
      | READONLY    | index.php?m=reports&a=generateJobOrderReportPDF&dataSet=9,7,3,1                    |        |
