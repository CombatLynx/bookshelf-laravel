<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Library;

use App\Domain\Library\Exception\InvalidIsbn;
use App\Domain\Library\Isbn;
use PHPUnit\Framework\TestCase;

final class IsbnTest extends TestCase
{
    public function test_hyphens_and_spaces_are_normalized(): void
    {
        $isbn = Isbn::fromString('978-0-321-12521-7');

        $this->assertSame('9780321125217', $isbn->toString());
        $this->assertSame('978-0-321-12521-7', $isbn->formatted());
    }

    public function test_checksum_is_enforced(): void
    {
        $this->expectException(InvalidIsbn::class);

        Isbn::fromString('9780321125215');
    }

    public function test_short_value_is_rejected(): void
    {
        $this->expectException(InvalidIsbn::class);

        Isbn::fromString('123');
    }
}
