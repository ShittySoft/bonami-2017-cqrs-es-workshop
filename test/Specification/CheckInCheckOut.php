<?php

namespace Specification;

use Behat\Behat\Context\Context;
use Behat\Behat\Definition\Call\Given;
use Building\Domain\Aggregate\Building;
use Prooph\EventSourcing\AggregateChanged;

final class CheckInCheckOut implements Context
{
    /**
     * @Given a new building was registered
     */
    public function a_new_building_was_registered() : void
    {
        throw new \InvalidArgumentException('blah');
    }

    /**
     * @When the user checks into the building
     */
    public function the_user_checks_into_the_building() : void
    {
        throw new \InvalidArgumentException('blah');
    }

    /**
     * @Then the user should have been checked into the building
     */
    public function the_user_should_have_been_checked_into_the_building() : void
    {
        throw new \InvalidArgumentException('blah');
    }

    private function addPastEvent() : void
    {
        throw new \InvalidArgumentException('blah');
    }

    private function aggregate() : Building
    {
        // TODO
    }

    private function nextRecordedEvent() : AggregateChanged
    {
        // TODO
    }
}
