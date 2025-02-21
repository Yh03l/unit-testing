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
        $email = Email::fromString('test@example.com');
        $this->assertEquals('test@example.com', $email->getValue());
    }

    public function testEmailEquality(): void
    {
        $email1 = Email::fromString('test@example.com');
        $email2 = Email::fromString('test@example.com');
        $email3 = Email::fromString('other@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    public function testToString(): void
    {
        $emailString = 'test@example.com';
        $email = Email::fromString($emailString);
        $this->assertEquals($emailString, (string)$email);
    }

    public function testTrimsWhitespace(): void
    {
        $email = Email::fromString('  test@example.com  ');
        $this->assertEquals('test@example.com', $email->getValue());
    }

    public function testThrowsExceptionForEmptyEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El email no puede estar vacío');
        Email::fromString('   ');
    }

    public function testThrowsExceptionForInvalidEmailFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('<invalid> no es un email válido');
        Email::fromString('invalid');
    }

    public function testThrowsExceptionForMultipleAtSymbols(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('<test@multiple@example.com> no es un email válido');
        Email::fromString('test@multiple@example.com');
    }

    public function testThrowsExceptionForLongLocalPart(): void
    {
        $longLocal = str_repeat('a', 65);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('<' . $longLocal . '@example.com> no es un email válido');
        Email::fromString($longLocal . '@example.com');
    }

    public function testThrowsExceptionForLongDomain(): void
    {
        $longDomain = str_repeat('a', 256);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('<test@' . $longDomain . '> no es un email válido');
        Email::fromString('test@' . $longDomain);
    }

    public function testThrowsExceptionForDomainStartingWithDot(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('<test@.example.com> no es un email válido');
        Email::fromString('test@.example.com');
    }

    public function testThrowsExceptionForDomainEndingWithDot(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('<test@example.com.> no es un email válido');
        Email::fromString('test@example.com.');
    }

    public function testThrowsExceptionForConsecutiveDotsInDomain(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('<test@example..com> no es un email válido');
        Email::fromString('test@example..com');
    }

    public function testThrowsExceptionForUnicodeCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('<test@éxample.com> no es un email válido');
        Email::fromString('test@éxample.com');
    }

    #[DataProvider('validEmailsProvider')]
    public function testAcceptsValidEmails(string $email): void
    {
        $emailObj = Email::fromString($email);
        $this->assertEquals($email, $emailObj->getValue());
    }

    public static function validEmailsProvider(): array
    {
        return [
            'simple email' => ['test@example.com'],
            'email with numbers' => ['test123@example.com'],
            'email with dots' => ['test.name@example.com'],
            'email with plus' => ['test+label@example.com'],
            'email with subdomain' => ['test@sub.example.com'],
            'email with hyphens' => ['test@example-domain.com'],
            'short email' => ['a@b.com'],
            'email with underscores' => ['test_name@example.com'],
        ];
    }

    #[DataProvider('invalidEmailsProvider')]
    public function testRejectsInvalidEmails(string $email): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Email::fromString($email);
    }

    public static function invalidEmailsProvider(): array
    {
        return [
            'empty string' => [''],
            'no at symbol' => ['testexample.com'],
            'multiple at symbols' => ['test@multiple@example.com'],
            'space in email' => ['test @example.com'],
            'invalid characters' => ['test$@example.com'],
            'missing domain' => ['test@'],
            'missing local part' => ['@example.com'],
            'domain starts with dot' => ['test@.example.com'],
            'domain ends with dot' => ['test@example.com.'],
            'consecutive dots in domain' => ['test@example..com'],
            'unicode characters' => ['test@éxample.com'],
        ];
    }
} 