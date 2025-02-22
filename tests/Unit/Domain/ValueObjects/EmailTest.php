<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use Commercial\Domain\ValueObjects\Email;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class EmailTest extends TestCase
{
    public function testFromStringWithValidEmail(): void
    {
        $value = 'test@example.com';
        $email = Email::fromString($value);
        $this->assertEquals($value, $email->getValue());
        $this->assertEquals($value, (string)$email);
    }

    public function testFromStringTrimsWhitespace(): void
    {
        $value = '  test@example.com  ';
        $email = Email::fromString($value);
        $this->assertEquals('test@example.com', $email->getValue());
        $this->assertEquals('test@example.com', (string)$email);
    }

    public function testEqualsReturnsTrueForSameValue(): void
    {
        $email1 = Email::fromString('test@example.com');
        $email2 = Email::fromString('test@example.com');
        $this->assertTrue($email1->equals($email2));
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        $email1 = Email::fromString('test1@example.com');
        $email2 = Email::fromString('test2@example.com');
        $this->assertFalse($email1->equals($email2));
    }

    #[DataProvider('invalidEmailProvider')]
    public function testFromStringThrowsExceptionForInvalidEmail(string $invalidEmail, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        Email::fromString($invalidEmail);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'empty string' => ['', 'El email no puede estar vacío'],
            'invalid format' => ['invalid-email', '<invalid-email> no es un email válido'],
            'missing @' => ['testemail.com', '<testemail.com> no es un email válido'],
            'multiple @' => ['test@email@example.com', '<test@email@example.com> no es un email válido'],
            'unicode characters' => ['test@exámple.com', '<test@exámple.com> no es un email válido'],
            'long local part' => [str_repeat('a', 65) . '@example.com', '<' . str_repeat('a', 65) . '@example.com> no es un email válido'],
            'long domain' => ['test@' . str_repeat('a', 256) . '.com', '<test@' . str_repeat('a', 256) . '.com> no es un email válido'],
            'domain starts with dot' => ['test@.example.com', '<test@.example.com> no es un email válido'],
            'domain ends with dot' => ['test@example.com.', '<test@example.com.> no es un email válido'],
            'consecutive dots in domain' => ['test@example..com', '<test@example..com> no es un email válido']
        ];
    }

    #[DataProvider('validEmailProvider')]
    public function testFromStringAcceptsValidEmail(string $validEmail): void
    {
        $email = Email::fromString($validEmail);
        $this->assertEquals($validEmail, $email->getValue());
    }

    public static function validEmailProvider(): array
    {
        return [
            'simple email' => ['test@example.com'],
            'email with numbers' => ['test123@example.com'],
            'email with dots' => ['test.user@example.com'],
            'email with plus' => ['test+user@example.com'],
            'email with underscore' => ['test_user@example.com'],
            'email with hyphen' => ['test-user@example.com'],
            'email with subdomain' => ['test@sub.example.com'],
            'email with multiple subdomains' => ['test@sub1.sub2.example.com'],
            'email with short TLD' => ['test@example.co'],
            'email with long TLD' => ['test@example.travel']
        ];
    }
} 