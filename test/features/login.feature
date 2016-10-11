@core
Feature: Login
  Access is required in order for my organization to be able to restric access to our opencats instance and information. 
  
  Scenario: Spoof session
    Given I am spoofing a session with "o964p0pr602975o0671qo50n1208r6nn" cookie
    And I am on "/index.php?m=joborders"
    And I should see "Username"
    And I should see "Password"
    And I should see "Login"
    And I should not see "Search Job Orders"
    And I should not see "Add Job Orders"
    
  Scenario: Login page fields
    Given I am on "/"
    And I should see "Username"
    And I should see "Password"
    And I should see "Login"

  Scenario: Login with non-existing user
    Given I am on "/"
    And I fill in "Username" with "invalid@username.com"
    And I fill in "Password" with "invalidpass"
    When I press "Login"
    Then I should see "Invalid username or password"

  Scenario: Login with invalid password
    Given I am on "/"
    And I fill in "Username" with "admin"
    And I fill in "Password" with "invalidpassword"
    When I press "Login"
    Then I should see "Invalid username or password"

  Scenario: Login as administrator
    Given I am on "/"
    And I fill in "Username" with "admin"
    And I fill in "Password" with "admin"
    When I press "Login"
    Then I should not see "Invalid username or password"
    And I should see "Administrator"
    And I should see "Logout"
    
  Scenario: Logout
    Given I am authenticated as "Administrator"
    And I am on "/"
    When I follow "Logout"
    Then I should not see "Logout"
    And I should not see "Administrator"
    And I should see "Login"
    And I should see "Username"
    And I should see "Password"
    
  Scenario: Access page after logout redirects to login
    Given I am authenticated as "Administrator"
    And I am on "/"
    And I follow "Logout"
    And I am on "/index.php?m=joborders"
    And I should see "Username"
    And I should see "Password"
    And I should see "Login"
    And I should not see "Search Job Orders"
    And I should not see "Add Job Orders"

    
    