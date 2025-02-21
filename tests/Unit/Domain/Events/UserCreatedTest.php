<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Events;

use Commercial\Domain\Events\UserCreated;
use PHPUnit\Framework\TestCase;

class UserCreatedTest extends TestCase
{
    private string $userId;
    private string $email;
    private UserCreated $event;

    protected function setUp(): void
    {
        $this->userId = 'test-user-id';
        $this->email = 'test@example.com';
        $this->event = new UserCreated($this->userId, $this->email);
    }

    public function testGetUserId(): void
    {
        $this->assertEquals($this->userId, $this->event->getUserId());
    }

    public function testGetEmail(): void
    {
        $this->assertEquals($this->email, $this->event->getEmail());
    }

    public function testGetOccurredOn(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->event->getOccurredOn());
    }
} 