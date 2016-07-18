Feature: Login
  Access is required in order for my organization to be able to restric access to our opencats instance and information. 
  
  Scenario: Spoof session
    Given I am spoofing a session with "o964p0pr602975o0671qo50n1208r6nn" cookie
    And I am on "/index.php?m=joborders"
    Then I should see "Welcome to opencats"
    And I should see "Username"
    And I should see "Password"
    And I should see "Login"
    And I should not see "Search Job Orders"
    And I should not see "Add Job Orders"
    

