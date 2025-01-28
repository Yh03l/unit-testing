<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use Commercial\Domain\ValueObjects\Email;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class EmailTest extends TestCase
{
    public function testCreateValidEmail(): void
    {
        $emailString = 'test@example.com';
        $email = Email::fromString($emailString);
        
        $this->assertEquals($emailString, $email->getValue());
    }

    public function testThrowsExceptionWhenEmailIsInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Email::fromString('invalid-email');
    }

    public function testEmailEquality(): void
    {
        $email1 = Email::fromString('test@example.com');
        $email2 = Email::fromString('test@example.com');
        $email3 = Email::fromString('other@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    #[DataProvider('validEmailProvider')]
    public function testAcceptsValidEmails(string $validEmail): void
    {
        $email = Email::fromString($validEmail);
        $this->assertEquals($validEmail, $email->getValue());
    }

    #[DataProvider('invalidEmailProvider')]
    public function testThrowsExceptionWithInvalidEmails(string $invalidEmail): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Email::fromString($invalidEmail);
    }

    public static function validEmailProvider(): array
    {
        return [
            'simple email' => ['test@example.com'],
            'email with dots' => ['test.name@example.com'],
            'email with plus' => ['test+label@example.com'],
            'email with exclamation' => ['test!important@example.com'],
            'email with hyphen' => ['test-name@example.com'],
            'email with underscore' => ['test_name@example.com'],
            'email with numbers' => ['test123@example.com'],
            'subdomain email' => ['test@sub.example.com'],
        ];
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'empty string' => [''],
            'without @' => ['invalidemail.com'],
            'without domain' => ['test@'],
            'with spaces' => ['test @example.com'],
            'multiple @' => ['test@@example.com'],
            'invalid domain format' => ['test@domain..com'],
            'only whitespace' => ['   '],
            'with unicode' => ['test🌟@example.com'],
            'domain starts with dot' => ['test@.example.com'],
            'domain ends with dot' => ['test@example.com.'],
            'consecutive dots' => ['test..name@example.com'],
        ];
    }
} 