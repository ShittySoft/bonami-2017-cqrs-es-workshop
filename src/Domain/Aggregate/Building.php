<?php

declare(strict_types=1);

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent\CheckInAnomalyDetected;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Building\Domain\DomainEvent\UserCheckedOut;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

final class Building extends AggregateRoot
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    /**
     * @var null[] indexed by username
     */
    private $checkedInUsers = [];

    public static function new(string $name) : self
    {
        $self = new self();

        $self->recordThat(NewBuildingWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'name' => $name
            ]
        ));

        return $self;
    }

    public function checkInUser(string $username) : void
    {
        $anomaly = \array_key_exists($username, $this->checkedInUsers);

        $this->recordThat(UserCheckedIn::fromBuildingIdAndUsername(
            $this->uuid,
            $username
        ));

        if ($anomaly) {
            $this->recordThat(CheckInAnomalyDetected::fromBuildingIdAndUsername(
                $this->uuid,
                $username
            ));
        }
    }

    public function checkOutUser(string $username) : void
    {
        $anomaly = ! \array_key_exists($username, $this->checkedInUsers);

        $this->recordThat(UserCheckedOut::fromBuildingIdAndUsername(
            $this->uuid,
            $username
        ));

        if ($anomaly) {
            $this->recordThat(CheckInAnomalyDetected::fromBuildingIdAndUsername(
                $this->uuid,
                $username
            ));
        }
    }

    protected function whenNewBuildingWasRegistered(NewBuildingWasRegistered $event) : void
    {
        $this->uuid = $event->uuid();
        $this->name = $event->name();
    }

    protected function whenUserCheckedIn(UserCheckedIn $event) : void
    {
        $this->checkedInUsers[$event->username()] = null;
    }

    protected function whenUserCheckedOut(UserCheckedOut $event) : void
    {
        unset($this->checkedInUsers[$event->username()]);
    }

    protected function whenCheckInAnomalyDetected(CheckInAnomalyDetected $event) : void
    {
        // empty
    }

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }
}
