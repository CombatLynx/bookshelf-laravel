<?php

declare(strict_types=1);

namespace App\Domain\Library;

use App\Domain\Library\Exception\InvalidBookData;
use DateTimeImmutable;

/** Бронь на книгу: очередь, пока экземпляр на руках, и срок, когда его уже отложили. */
final class Reservation
{
    public const WAITING = 'waiting';

    public const READY = 'ready';

    /** @var ReaderId */
    private $reader;

    /** @var BorrowerName */
    private $name;

    /** @var DateTimeImmutable */
    private $placedOn;

    /** @var DateTimeImmutable|null */
    private $expiresOn;

    /** @var string */
    private $phase;

    private function __construct(
        ReaderId $reader,
        BorrowerName $name,
        DateTimeImmutable $placedOn,
        ?DateTimeImmutable $expiresOn,
        string $phase
    ) {
        $this->reader = $reader;
        $this->name = $name;
        $this->placedOn = $placedOn;
        $this->expiresOn = $expiresOn;
        $this->phase = $phase;
    }

    public static function waiting(ReaderId $reader, BorrowerName $name, DateTimeImmutable $on): self
    {
        return new self($reader, $name, self::atMidnight($on), null, self::WAITING);
    }

    public static function restore(
        ReaderId $reader,
        BorrowerName $name,
        DateTimeImmutable $placedOn,
        ?DateTimeImmutable $expiresOn,
        string $phase
    ): self {
        if (!in_array($phase, [self::WAITING, self::READY], true)) {
            throw InvalidBookData::inconsistentLoan();
        }

        $placedOn = self::atMidnight($placedOn);
        $expiresOn = $expiresOn === null ? null : self::atMidnight($expiresOn);

        if ($phase === self::WAITING && $expiresOn !== null) {
            throw InvalidBookData::inconsistentLoan();
        }

        if ($phase === self::READY && ($expiresOn === null || $expiresOn < $placedOn)) {
            throw InvalidBookData::inconsistentLoan();
        }

        return new self($reader, $name, $placedOn, $expiresOn, $phase);
    }

    public function markReady(DateTimeImmutable $on, ReservationPolicy $policy): self
    {
        $readyOn = self::atMidnight($on);

        return new self($this->reader, $this->name, $this->placedOn, $policy->expiresOn($readyOn), self::READY);
    }

    public function isExpired(DateTimeImmutable $on): bool
    {
        return $this->expiresOn !== null && self::atMidnight($on) > $this->expiresOn;
    }

    public function isHeldBy(ReaderId $reader): bool
    {
        return $this->reader->equals($reader);
    }

    public function isReady(): bool
    {
        return $this->phase === self::READY;
    }

    public function isWaiting(): bool
    {
        return $this->phase === self::WAITING;
    }

    public function readerId(): ReaderId
    {
        return $this->reader;
    }

    public function name(): BorrowerName
    {
        return $this->name;
    }

    public function placedOn(): DateTimeImmutable
    {
        return $this->placedOn;
    }

    public function expiresOn(): ?DateTimeImmutable
    {
        return $this->expiresOn;
    }

    public function phase(): string
    {
        return $this->phase;
    }

    private static function atMidnight(DateTimeImmutable $moment): DateTimeImmutable
    {
        return $moment->setTime(0, 0);
    }
}
