<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Library\Book;
use App\Domain\Library\BookId;
use App\Domain\Library\BookRepository;
use App\Domain\Library\BookStatus;
use App\Domain\Library\BorrowerName;
use App\Domain\Library\Exception\InvalidBookData;
use App\Domain\Library\Isbn;
use App\Domain\Library\Loan;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class EloquentBookRepository implements BookRepository
{
    public function nextIdentity(): BookId
    {
        return BookId::fromString(Uuid::uuid4()->toString());
    }

    public function save(Book $book): void
    {
        $loan = $book->loan();

        BookRecord::query()->updateOrCreate(
            ['id' => $book->id()->toString()],
            [
                'title' => $book->title(),
                'author' => $book->author(),
                'isbn' => $book->isbn()->toString(),
                'status' => $book->status()->value(),
                'borrower_name' => $loan === null ? null : $loan->borrower()->toString(),
                'borrowed_on' => $loan === null ? null : $loan->borrowedOn()->format('Y-m-d'),
                'due_on' => $loan === null ? null : $loan->dueOn()->format('Y-m-d'),
                'renewals' => $loan === null ? 0 : $loan->renewals(),
            ]
        );
    }

    public function findById(BookId $id): ?Book
    {
        $record = BookRecord::query()->find($id->toString());

        return $record instanceof BookRecord ? $this->map($record) : null;
    }

    public function findByIsbn(Isbn $isbn): ?Book
    {
        $record = BookRecord::query()->where('isbn', $isbn->toString())->first();

        return $record instanceof BookRecord ? $this->map($record) : null;
    }

    public function all(): array
    {
        return BookRecord::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(function (BookRecord $record) {
                return $this->map($record);
            })
            ->all();
    }

    private function map(BookRecord $record): Book
    {
        $status = BookStatus::fromString((string) $record->getAttribute('status'));

        return Book::reconstitute(
            BookId::fromString((string) $record->getKey()),
            (string) $record->getAttribute('title'),
            (string) $record->getAttribute('author'),
            Isbn::fromString((string) $record->getAttribute('isbn')),
            $status,
            $this->loanFrom($record, $status)
        );
    }

    private function loanFrom(BookRecord $record, BookStatus $status): ?Loan
    {
        if ($status->isAvailable()) {
            return null;
        }

        $name = $record->getAttribute('borrower_name');
        $borrowedOn = $record->getAttribute('borrowed_on');
        $dueOn = $record->getAttribute('due_on');

        if (!is_string($name) || $name === '' || $borrowedOn === null || $dueOn === null) {
            throw InvalidBookData::inconsistentLoan();
        }

        return Loan::restore(
            BorrowerName::fromString($name),
            new DateTimeImmutable((string) $borrowedOn),
            new DateTimeImmutable((string) $dueOn),
            (int) $record->getAttribute('renewals')
        );
    }
}
