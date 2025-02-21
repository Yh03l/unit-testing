<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Events;

use Commercial\Domain\Events\UserUpdated;
use PHPUnit\Framework\TestCase;

class UserUpdatedTest extends TestCase
{
    private string $userId;
    private UserUpdated $event;

    protected function setUp(): void
    {
        $this->userId = 'test-user-id';
        $this->event = new UserUpdated($this->userId);
    }

    public function testGetUserId(): void
    {
        $this->assertEquals($this->userId, $this->event->getUserId());
    }

    public function testGetOccurredOn(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->event->getOccurredOn());
    }
} 