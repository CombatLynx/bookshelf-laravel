<?php

declare(strict_types=1);

namespace App\Domain\Library;

/**
 * Порт хранилища. Домен описывает, что ему нужно, а Eloquent-реализация
 * живёт в инфраструктурном слое.
 */
interface BookRepository
{
    public function nextIdentity(): BookId;

    public function save(Book $book): void;

    public function findById(BookId $id): ?Book;

    public function findByIsbn(Isbn $isbn): ?Book;

    /**
     * @return Book[]
     */
    public function all(): array;
}
