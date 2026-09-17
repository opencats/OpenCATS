@core @reports
Feature: Reports presentation and existing output workflows
  Background:
    Given I am authenticated as "Administrator"

  @reports_statistics
  Scenario: Statistics and period links
    When I am on "/index.php?m=reports"
    Then I should see "Today"
    And I should see "Yesterday"
    And I should see "This Week"
    And I should see "Last Week"
    And I should see "This Month"
    And I should see "Last Month"
    And I should see "This Year"
    And I should see "Last Year"
    And I should see "To Date"
    And Reports preserves all period links
    And Reports totals match the existing records

  @reports_output
  # Application output completion is blocked by #885 (missing report footer).
  # Exercise the real templates in isolation; do not claim route completion.
  Scenario Outline: Submission and placement templates preserve titles and empty results
    When I render the isolated "<kind>" report with title "<title>" and "empty" results
    Then the isolated report heading is "<title>" with category "<kind>"
    And the isolated report has no result tables
    Examples:
      | title              | kind        |
      | Today's Report     | Submissions |
      | To Date Report     | Submissions |
      | Last Week's Report | Placements  |
      | To Date Report     | Placements  |

  @reports_joborder
  Scenario: Job order form retains PDF request parameters
    When I am on "/index.php?m=reports&a=customizeJobOrderReport&jobOrderID=40001"
    Then I should see "Reports: Job Order Report"
    And the "jobOrderName" field should contain "OpenCATS Tester"
    When I fill in "siteName" with "Reports test site"
    And I fill in "notes" with "Reports test notes"
    Then Reports form submits "generateJobOrderReportPDF" using GET
    And Reports has field "ext" with value ".pdf"
    And I should see a "input[type=reset][value=Reset]" element

  @reports_eeo
  Scenario: EEO preview preserves selected filters
    When I am on "/index.php?m=reports&a=customizeEEOReport"
    Then I should see "Work In Progress"
    And Reports form submits "generateEEOReportPreview" using GET
    When I choose Reports radio "period" value "month"
    And I choose Reports radio "status" value "placed"
    And I press "Preview Report"
    Then I should see "Report Preview:"
    And Reports has field "period" with value "month"
    And Reports has field "status" with value "placed"
    And the response status code should be 200

  @reports_graph
  Scenario: Standalone graph retains image and close control
    When I am on "/index.php?m=reports&a=graphView&theImage=images/noDataByGender.png"
    Then I should see "Graph refreshes every 5 minutes"
    And I should see "Close Window"
    And I should see a "img[src='images/noDataByGender.png']" element

  @reports_error
  Scenario: Invalid job order report retains error handling
    When I am on "/index.php?m=reports&a=customizeJobOrderReport&jobOrderID=invalid"
    Then I should see "Bad Server Information"

  @reports_rows
  Scenario Outline: Isolated report tables preserve columns and ordered fixture rows
    When I render the isolated "<kind>" report with title "To Date Report" and "populated" results
    Then the isolated report heading is "To Date Report" with category "<kind>"
    And the isolated report job heading is "Fixture Job at Fixture Company (Fixture Owner)"
    And the isolated report table contains exactly:
      | First Name | Last Name | Candidate Owner | <dateHeading>       |
      | Reports    | Fixture   | Owner & Team    | 03-02-20 (12:00 PM)  |
      | Second     | Candidate | Another Owner   | 04-02-20 (01:30 PM)  |
    Examples:
      | kind        | dateHeading    |
      | Submissions | Date Submitted |
      | Placements  | Date Placed    |

  @javascript @reports_joborder
  Scenario: Dataset components update in order and reset controls remain functional
    When I am on "/index.php?m=reports&a=customizeJobOrderReport&jobOrderID=40001"
    And I fill in "dataSet1" with "9"
    And I fill in "dataSet2" with "7"
    And I fill in "dataSet3" with "3"
    And I fill in "dataSet4" with "1"
    And I fill in "notes" with "Report notes"
    Then Reports dataset is "9,7,3,1"
    And Reports form submits "generateJobOrderReportPDF" using GET
    Given I capture the Reports PDF form submission
    When I press "Generate Report"
    Then the Reports PDF request preserves the entered parameters
    When I press "Reset"
    Then the "notes" field should contain ""
    And the "jobOrderName" field should contain "OpenCATS Tester"
