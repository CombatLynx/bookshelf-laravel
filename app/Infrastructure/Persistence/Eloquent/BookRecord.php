<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

/** Строка таблицы books. Это не доменная модель, а деталь хранения. */
class BookRecord extends Model
{
    protected $table = 'books';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];
}
