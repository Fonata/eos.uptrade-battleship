Feature: Provide an API for user login and CRUD

  As a JSON REST API developer
  I need Create, Read, Update, and Delete (CRUD) operations for users.
  I don't want regular users to be able to see or change other users.

  Scenario: Logging in with wrong email
    Given the "Content-Type" request header is "application/json"
    And the request body is:
      """
      {
          "email": "data",
          "password": "wrong"
      }
      """
    When I request "/login" using HTTP POST
    Then the response body contains JSON:
      """
      {
        "status": "error"
      }
      """
    And the response code is 403

  Scenario: Logging in with wrong password
    Given the "Content-Type" request header is "application/json"
    And the request body is:
      """
      {
          "email": "admin@eos-uptrade.de",
          "password": "wrong"
      }
      """
    When I request "/login" using HTTP POST
    Then the response body contains JSON:
      """
      {
        "status": "error"
      }
      """
    And the response code is 403



  Scenario: Logging in with correct password
    Given the "Content-Type" request header is "application/json"
    And the request body is:
      """
      {
          "email": "admin@eos-uptrade.de",
          "password": "demo"
      }
      """
    When I request "/login" using HTTP POST
    Then the response body contains JSON:
      """
      {
        "status": "ok"
      }
      """
    And the response code is 200

  Scenario: Creating another user
    Given I am logged in as "admin@eos-uptrade.de"
    Given the "Content-Type" request header is "application/json"
    Given the "Accept" request header is "application/ld+json"
    And the request body is:
      """
      {
        "name": "Christian",
        "surname": "Bläul",
        "email": "christian@blaeul.de",
        "roles": [
          "ROLE_USER"
        ],
        "password": "string"
      }
      """
    When I request "/api/users" using HTTP POST
    Then the response body contains JSON:
      """
      {
        "@context": "/contexts/User",
        "@id": "/api/users/2",
        "@type": "http://schema.org/Person",
        "id": 2,
        "name": "Christian",
        "surname": "Bläul",
        "email": "christian@blaeul.de",
        "roles": [
          "ROLE_USER"
        ],
        "games": []
      }
      """
    And the response code is 201

    # Attempt to crate the user again:
    Given the request body is:
      """
      {
        "name": "Christian",
        "surname": "Bläul",
        "email": "christian@blaeul.de",
        "roles": [
          "ROLE_USER"
        ],
        "password": "string"
      }
      """
    When I request "/api/users" using HTTP POST
    Then the response body contains JSON:
      """
      {
          "hydra:title": "An error occurred",
          "hydra:description": "email: This value is already used."
       }
      """
    And the response code is 400

    # List users - there should be 2
    Given the "Accept" request header is "application/json"
    When I request "/api/users" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
      """
      [
          {
              "id": 1,
              "name": "Adam",
              "surname": "Admin",
              "email": "admin@eos-uptrade.de",
              "roles": [
                  "ROLE_ADMIN",
                  "ROLE_USER"
              ],
              "games": []
          },
          {
              "id": 2,
              "name": "Christian",
              "surname": "Bl\u00e4ul",
              "email": "christian@blaeul.de",
              "roles": [
                  "ROLE_USER"
              ],
              "games": []
          }
      ]
       """

    When I request "/api/users/2" using HTTP DELETE
    Then the response code is 204

    # List users again - only the admin should remain
    When I request "/api/users" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
      """
      [
          {
              "id": 1,
              "name": "Adam",
              "surname": "Admin",
              "email": "admin@eos-uptrade.de",
              "roles": [
                  "ROLE_ADMIN",
                  "ROLE_USER"
              ],
              "games": []
          }
      ]
       """

  Scenario: Get an existing user without logging in
    When I request "/api/users/1" using HTTP GET
    Then the response code is 403

  Scenario: Get an existing user after logging in
    Given I am logged in as "admin@eos-uptrade.de"
    When I request "/api/users/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
      """
          {
              "id": 1,
              "name": "Adam",
              "surname": "Admin",
              "email": "admin@eos-uptrade.de",
              "roles": [
                  "ROLE_ADMIN",
                  "ROLE_USER"
              ],
              "games": []
          }
       """

  Scenario: Deleting a non-existing user
    Given I am logged in as "admin@eos-uptrade.de"
    When I request "/api/users/4" using HTTP DELETE
    Then the response body contains JSON:
      """
      {
        "hydra:title": "An error occurred",
        "hydra:description": "Not Found"
      }
      """
    And the response code is 404

  # Test if UserAttributePermissionChecker::userHasPermissionsForUser works.
  Scenario: Non-admins cannot see email addresses or other users
    Given I am logged in as "regular-user@eos-uptrade.de"
    When I request "/api/users/1" using HTTP GET
    Then the response code is 403

    # Load own user record
    When I request "/api/users/2" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
      """
      {
          "id": 2,
          "name": "regular-user",
          "surname": "M\u00fcller",
          "roles": [
              "ROLE_USER"
          ],
          "games": []
      }
      """

    # Non-admin cannot change anything
    When the request body is:
      """
      {
        "name": "Christian",
        "surname": "Gruchow",
        "email": "christian@blaeul.de",
        "roles": [
          "ROLE_USER"
        ],
        "password": "string"
      }
      """
    And I request "/api/users/2" using HTTP PUT
    Then the response code is 403

    # As an admin I can change the user data
    Given I am logged in as "admin@eos-uptrade.de"
    And the "Content-Type" request header is "application/json"
    When the request body is:
      """
      {
        "name": "Christian",
        "surname": "Gruchow",
        "email": "christian@blaeul.de",
        "roles": [
          "ROLE_USER"
        ],
        "password": "string"
      }
      """
    And I request "/api/users/2" using HTTP PUT
    Then the response code is 200

    # Test if the change is applied:
    When I request "/api/users/2" using HTTP GET
    Then the response code is 200
    """
      {
        "name": "Christian",
        "surname": "Gruchow",
        "email": "christian@blaeul.de",
        "roles": [
          "ROLE_USER"
        ],
      }
    """
