<?php

declare(strict_types=1);

namespace App\Domain\Library;

use App\Domain\Library\Exception\InvalidBookData;
use App\Domain\Library\Exception\LoanCannotBeRenewed;
use DateTimeImmutable;

/** Срок, на который книга находится у читателя. Часть агрегата «Книга». */
final class Loan
{
    /** @var BorrowerName */
    private $borrower;

    /** @var DateTimeImmutable */
    private $borrowedOn;

    /** @var DateTimeImmutable */
    private $dueOn;

    /** @var int */
    private $renewals;

    private function __construct(
        BorrowerName $borrower,
        DateTimeImmutable $borrowedOn,
        DateTimeImmutable $dueOn,
        int $renewals
    ) {
        $this->borrower = $borrower;
        $this->borrowedOn = $borrowedOn;
        $this->dueOn = $dueOn;
        $this->renewals = $renewals;
    }

    public static function open(BorrowerName $borrower, DateTimeImmutable $on, LendingPolicy $policy): self
    {
        $borrowedOn = self::atMidnight($on);

        return new self($borrower, $borrowedOn, $policy->dueOn($borrowedOn), 0);
    }

    public static function restore(
        BorrowerName $borrower,
        DateTimeImmutable $borrowedOn,
        DateTimeImmutable $dueOn,
        int $renewals
    ): self {
        $borrowedOn = self::atMidnight($borrowedOn);
        $dueOn = self::atMidnight($dueOn);

        if ($dueOn < $borrowedOn || $renewals < 0 || $renewals > LendingPolicy::MAX_RENEWALS) {
            throw InvalidBookData::inconsistentLoan();
        }

        return new self($borrower, $borrowedOn, $dueOn, $renewals);
    }

    public function renew(DateTimeImmutable $on, LendingPolicy $policy, string $title): self
    {
        if ($this->isOverdue($on)) {
            throw LoanCannotBeRenewed::overdue($title);
        }

        if (!$policy->allowsAnotherRenewal($this->renewals)) {
            throw LoanCannotBeRenewed::limitReached($title);
        }

        return new self($this->borrower, $this->borrowedOn, $policy->dueOn($on), $this->renewals + 1);
    }

    public function isOverdue(DateTimeImmutable $on): bool
    {
        return self::atMidnight($on) > $this->dueOn;
    }

    public function borrower(): BorrowerName
    {
        return $this->borrower;
    }

    public function borrowedOn(): DateTimeImmutable
    {
        return $this->borrowedOn;
    }

    public function dueOn(): DateTimeImmutable
    {
        return $this->dueOn;
    }

    public function renewals(): int
    {
        return $this->renewals;
    }

    private static function atMidnight(DateTimeImmutable $moment): DateTimeImmutable
    {
        return $moment->setTime(0, 0);
    }
}
