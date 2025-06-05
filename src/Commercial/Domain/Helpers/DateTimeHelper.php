<?php

declare(strict_types=1);

namespace Commercial\Domain\Helpers;

class DateTimeHelper
{
	private const TIMEZONE = 'UTC';

	public static function now(): \DateTimeImmutable
	{
		return new \DateTimeImmutable('now', new \DateTimeZone(self::TIMEZONE));
	}
}
