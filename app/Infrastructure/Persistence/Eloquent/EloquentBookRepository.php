<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Library\Book;
use App\Domain\Library\BookId;
use App\Domain\Library\BookRepository;
use App\Domain\Library\BookStatus;
use App\Domain\Library\Isbn;
use Ramsey\Uuid\Uuid;

final class EloquentBookRepository implements BookRepository
{
    public function nextIdentity(): BookId
    {
        return BookId::fromString(Uuid::uuid4()->toString());
    }

    public function save(Book $book): void
    {
        BookRecord::query()->updateOrCreate(
            ['id' => $book->id()->toString()],
            [
                'title' => $book->title(),
                'author' => $book->author(),
                'isbn' => $book->isbn()->toString(),
                'status' => $book->status()->value(),
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
        return Book::reconstitute(
            BookId::fromString((string) $record->getKey()),
            (string) $record->getAttribute('title'),
            (string) $record->getAttribute('author'),
            Isbn::fromString((string) $record->getAttribute('isbn')),
            BookStatus::fromString((string) $record->getAttribute('status'))
        );
    }
}
