<?php

namespace Database\Seeders;

use App\Application\Library\Exception\DuplicateIsbn;
use App\Application\Library\RegisterBookHandler;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $register = $this->container->make(RegisterBookHandler::class);

        $catalog = [
            ['Domain-Driven Design', 'Eric Evans', '9780321125217'],
            ['Clean Code', 'Robert C. Martin', '9780132350884'],
            ['Design Patterns', 'Erich Gamma', '9780201633610'],
        ];

        foreach ($catalog as $book) {
            try {
                $register->handle($book[0], $book[1], $book[2]);
            } catch (DuplicateIsbn $exception) {
                // Повторный посев не дублирует уже стоящие на полке книги.
            }
        }
    }
}
