Feature: Provide an API that to play the game "Battleship"

  As a JSON REST API developer
  I don't want games without owners.
  I want regular users to play the game.

  Scenario: Creating a game
    Given I am logged in as "regular-user@eos-uptrade.de"
    And the "Content-Type" request header is "application/json"
    Given the "Accept" request header is "application/json"
    And the request body is:
      """
      {
          "seed": 1
      }
      """
    When I request "/api/games" using HTTP POST
    Then the response body contains JSON:
      """
      {
          "id": 1,
          "owner": "/api/users/2",
          "ships": {
              "destroyer": ["B2", "C2"]
          }
      }
      """
    And the response code is 201

    # There should be 2 game records now (one for the human, one for the computer)
    Given I am logged in as "admin@eos-uptrade.de"
    When I request "/api/games" using HTTP GET
    Then the response code is 200
    And the response body is a JSON array of length 2

    Given I am logged in as "regular-user@eos-uptrade.de"
    When I request "/api/users/2" using HTTP GET
    Then the response body contains JSON:
    """
    {
        "games": [
            "/api/games/1"
        ]
    }
    """
    And the response code is 200

    # Schiff bewegen - aber die neue Stelle ist die alte:
    Given the request body is:
      """
      {
          "destroyer": ["B2", "C2"]
      }
      """
    When I request "/api/games/1/move-ship" using HTTP POST
    Then the response body contains JSON:
      """
      {
          "id": 1,
          "owner": "/api/users/2",
          "ships": {
              "destroyer": ["B2", "C2"]
          }
      }
      """
    And the response code is 200

    # Schiff bewegen - auf eine besetzte Stelle:
    Given the request body is:
      """
      {
          "destroyer": ["B2", "B3"]
      }
      """
    When I request "/api/games/1/move-ship" using HTTP POST
    Then the response body contains JSON:
      """
      {
          "detail": "The position B3 is taken."
      }
      """
    And the response code is 400

    # Schiff zu lang
    Given the request body is:
      """
      {
          "destroyer": ["B2", "B3", "B4"]
      }
      """
    When I request "/api/games/1/move-ship" using HTTP POST
    Then the response body contains JSON:
      """
      {
          "detail": "destroyer should have length 2, but 3 positions were POSTed."
      }
      """
    And the response code is 400

    # Schiff bewegen - auf eine wirklich neue Stelle:
    Given the request body is:
      """
      {
          "destroyer": ["A1", "A2"]
      }
      """
    When I request "/api/games/1/move-ship" using HTTP POST
    Then the response body contains JSON:
      """
      {
          "id": 1,
          "owner": "/api/users/2",
          "ships": {
              "destroyer": ["A1", "A2"]
          }
      }
      """
    And the response code is 200

    # erster Schuss - ungültiges Target
    Given the request body is:
      """
      {
          "target": "Z2"
      }
      """
    When I request "/api/games/1/shoot" using HTTP POST
    Then the response body contains JSON:
      """
      {
          "detail": "Target should be a letter and a number."
      }
      """
    And the response code is 400

    # erster Schuss:
    Given the request body is:
      """
      {
          "target": "A2"
      }
      """
    When I request "/api/games/1/shoot" using HTTP POST
    Then the response body contains JSON:
      """
      {
          "id": 1,
          "last_shot_result": "Hit. Destroyer",
          "last_shot_target": "A1"
      }
      """
    And the response code is 200

    # auf fremdes Spielfeld schießen:
    Given the request body is:
      """
      {
          "target": "A2"
      }
      """
    When I request "/api/games/2/shoot" using HTTP POST
    Then the response body contains JSON:
      """
      {
          "detail": "This is not your game."
      }
      """
    And the response code is 403


    # Schiff nach Spielstart bewegen - darf nicht klappen
    Given the request body is:
      """
      {
          "destroyer": ["J1", "J2"]
      }
      """
    When I request "/api/games/1/move-ship" using HTTP POST
    Then the response body contains JSON:
      """
      {
          "detail": "You cannot move ships after the game has started."
      }
      """
    And the response code is 400
