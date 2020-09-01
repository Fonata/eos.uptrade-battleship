Feature: Provide a front page

  In order to make the coding challenge accessible, I need a front page
  guiding the eos.uptrade staff where to look.

  Scenario: Load the front page
    Given I request "/" using HTTP GET
    Then the response code is 200
    And the response body matches:
    """
    /Christian/
    """


