@core @charts
Feature: Chart data remains accessible on the containing pages
  Background:
    Given I am authenticated as "Administrator"

  Scenario Outline: Retained Hiring Overview endpoint reflects recorded transitions for each range
    Given Hiring Overview has recorded transitions
    When I am on "/index.php?m=home&a=hiringOverview&view=<view>"
    Then chart "hiring-<view>" contains "3" series and "4" categories
    And Hiring Overview "<view>" agrees with the test database
    When another Hiring Overview hire is recorded
    And I am on "/index.php?m=home&a=hiringOverview&view=<view>"
    Then Hiring Overview "<view>" agrees with the test database
    Examples:
      | view |
      | 0    |
      | 1    |
      | 2    |

  Scenario: Job Order and expanded report retain pipeline categories
    When I am on "/index.php?m=joborders&a=show&jobOrderID=40001"
    Then chart "joborder-pipeline" contains "1" series and "10" categories
    And I should see "% of pipeline"
    When I follow "Expand chart"
    Then chart "expanded-pipeline" contains "1" series and "10" categories
    And I should see "Close Window"

  Scenario Outline: All EEO chart families render with the existing filters
    Given EEO chart panels are enabled for this scenario
    When I am on "/index.php?m=reports&a=generateEEOReportPreview&period=<period>&status=<status>"
    Then all EEO charts retain their recorded categories
    And Reports has field "period" with value "<period>"
    And Reports has field "status" with value "<status>"
    Examples:
      | period | status   |
      | all    | all      |
      | week   | rejected |
      | month  | placed   |

  Scenario: Recruiting summary downloads without a graph image request
    When I am on "/index.php?m=reports&a=generateJobOrderReportPDF&dataSet=9,7,3,1&siteName=Chart%20review&companyName=Example%20Company&jobOrderName=Recruiter&periodLine=September%202026&accountManager=Alex%20Example&recruiter=Sam%20Example&notes=Chart%20migration%20verification"
    Then the recruiting summary is a downloadable PDF
