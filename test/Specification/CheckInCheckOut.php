<?php

namespace Specification;

use Assert\Assertion;
use Behat\Behat\Context\Context;
use Behat\Behat\Definition\Call\Given;
use Building\Domain\Aggregate\Building;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Rhumsaa\Uuid\Uuid;

final class CheckInCheckOut implements Context
{
    /**
     * @var AggregateChanged[]
     */
    private $pastEvents = [];

    /**
     * @var Uuid
     */
    private $buildingId;

    /**
     * @var Building
     */
    private $building;

    /**
     * @var AggregateChanged[]|null
     */
    private $recordedEvents;

    public function __construct()
    {
        $this->buildingId = Uuid::uuid4();
    }

    /**
     * @Given a new building was registered
     */
    public function a_new_building_was_registered() : void
    {
        $this->addPastEvent(NewBuildingWasRegistered::occur(
            $this->buildingId->toString(),
            ['name' => 'A building, doesn\'t matter']
        ));
    }

    /**
     * @When the user checks into the building
     */
    public function the_user_checks_into_the_building() : void
    {
        $this->aggregate()->checkInUser('the user');
    }

    /**
     * @Then the user should have been checked into the building
     */
    public function the_user_should_have_been_checked_into_the_building() : void
    {
        Assertion::isInstanceOf($this->nextRecordedEvent(), UserCheckedIn::class);
    }

    private function addPastEvent(AggregateChanged $pastEvent) : void
    {
        $this->pastEvents[] = $pastEvent;
    }

    private function aggregate() : Building
    {
        if ($this->building) {
            return $this->building;
        }

        return $this->building = (new AggregateTranslator())
            ->reconstituteAggregateFromHistory(
                AggregateType::fromString(Building::class),
                new \ArrayIterator($this->pastEvents)
            );
    }

    private function nextRecordedEvent() : AggregateChanged
    {
        if (null === $this->recordedEvents) {
            $this->recordedEvents = (new AggregateTranslator())
                ->extractPendingStreamEvents($this->aggregate());
        }

        return array_shift($this->recordedEvents);
    }
}
