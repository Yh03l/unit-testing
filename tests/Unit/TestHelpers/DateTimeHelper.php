<?php

declare(strict_types=1);

namespace Tests\Unit\TestHelpers;

class DateTimeHelper
{
    private static ?\DateTimeImmutable $mockedNow = null;

    public static function mockNow(\DateTimeImmutable $now): void
    {
        self::$mockedNow = $now;
    }

    public static function now(): \DateTimeImmutable
    {
        return self::$mockedNow ?? new \DateTimeImmutable();
    }

    public static function reset(): void
    {
        self::$mockedNow = null;
    }
} 