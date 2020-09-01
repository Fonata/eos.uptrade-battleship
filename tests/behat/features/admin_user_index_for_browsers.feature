Feature: Provide a browser-based user administration

  For the eos.uptrade team to set up test users, it is useful
  not to have to jump to the API to create users.

  Scenario: Logging in with wrong password
    Given I am on "/login-form"
    When I fill in the following:
      | email    | bar |
      | password | foo |
    And I press "Sign in"
    Then I should be on "/login-form"
    And I should see "Email could not be found"

  Scenario: Creating and deleting an API user
    # Logging in with correct admin password
    Given I am on "/login-form"
    When I fill in the following:
      | email    | admin@eos-uptrade.de |
      | password | demo                |
    And I press "Sign in"
    Then I should be on "/"
    # Login successful:
    And I should see "eos.uptrade coding challenge submission"

    Given I am on "/login-form"
    Then I should see "Log out"

    # Attempt to create a user with an existing email address
    Given I am on "/admin/user"
    And I click the '[href="/admin/user/new"]' element
    When I fill in the following:
      | user[name]     | Zara                |
      | user[surname]  | Zimmermann          |
      | user[email]    | admin@eos-uptrade.de |
      | user[password] | funky               |
    And I press "Create new user"
    Then I should see "This value is already used."

    # Create a user with a new email address
    When I fill in the following:
      | user[email]    | zara@eos-uptrade.de |
      | user[password] | funky              |
    And I press "Create new user"
    Then I should see "Only admins can see this list."

    # Attempt to log in with an invalid password
    Given I am on "/logout"
    Given I am on "/login-form"
    When I fill in the following:
      | email    | zara@eos-uptrade.de         |
      | password | this-is-the-wrong-password |
    And I press "Sign in"
    Then I should see "Invalid credentials."

    # Attempt to log in with a correct password
    Given I am on "/logout"
    Given I am on "/login-form"
    When I fill in the following:
      | email    | zara@eos-uptrade.de         |
      | password | funky |
    And I press "Sign in"
    # Login successful:
    Then I should see "eos.uptrade coding challenge submission"

    # Zara doesn't have the ROLE_ADMIN.
    Given I am on "/admin/user"
    Then I should see "Access Denied."

    # Log in with correct admin password and
    # delete the newly created user
    Given I am on "/logout"
    And I am on "/login-form"
    And I fill in the following:
      | email    | admin@eos-uptrade.de |
      | password | demo                |
    And I press "Sign in"
    When I am on "/admin/user"
    And I click the '[data-email="zara@eos-uptrade.de"]' element
    And I press "Delete"
    Then I should see "Only admins can see this list."
    And I should not see "zara@eos-uptrade.de"
