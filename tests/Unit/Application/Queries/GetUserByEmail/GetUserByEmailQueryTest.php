<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\GetUserByEmail;

use Commercial\Application\Queries\GetUserByEmail\GetUserByEmailQuery;
use Commercial\Domain\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

class GetUserByEmailQueryTest extends TestCase
{
    private GetUserByEmailQuery $query;
    private string $email;

    protected function setUp(): void
    {
        $this->email = 'test@example.com';
        $this->query = new GetUserByEmailQuery($this->email);
    }

    public function testGetEmail(): void
    {
        $this->assertEquals(Email::fromString($this->email), $this->query->getEmail());
    }

    public function testConstructorValidatesEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetUserByEmailQuery('invalid-email');
    }
} 