Feature: Check-in and check-out

  Scenario: Check-in
    Given a new building was registered
    When the user checks into the building
    Then the user should have been checked into the building

