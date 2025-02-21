<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use Commercial\Domain\ValueObjects\ContractDate;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class ContractDateTest extends TestCase
{
    private DateTimeImmutable $tomorrow;
    private DateTimeImmutable $nextWeek;
    
    protected function setUp(): void
    {
        $this->tomorrow = new DateTimeImmutable('tomorrow');
        $this->nextWeek = new DateTimeImmutable('next week');
    }

    public function testCreateValidContractDate(): void
    {
        $contractDate = new ContractDate($this->tomorrow, $this->nextWeek);
        
        $this->assertEquals($this->tomorrow, $contractDate->getFechaInicio());
        $this->assertEquals($this->nextWeek, $contractDate->getFechaFin());
    }

    public function testCreateContractDateWithoutEndDate(): void
    {
        $contractDate = new ContractDate($this->tomorrow);
        
        $this->assertEquals($this->tomorrow, $contractDate->getFechaInicio());
        $this->assertNull($contractDate->getFechaFin());
    }

    public function testThrowsExceptionForPastStartDate(): void
    {
        $pastDate = new DateTimeImmutable('yesterday');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La fecha de inicio no puede ser en el pasado');
        
        new ContractDate($pastDate);
    }

    public function testThrowsExceptionWhenEndDateIsBeforeStartDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La fecha de fin debe ser posterior a la fecha de inicio');
        
        new ContractDate($this->nextWeek, $this->tomorrow);
    }

    public function testContractDateEquality(): void
    {
        $date1 = new ContractDate($this->tomorrow, $this->nextWeek);
        $date2 = new ContractDate($this->tomorrow, $this->nextWeek);
        $date3 = new ContractDate($this->tomorrow);

        $this->assertTrue($date1->equals($date2));
        $this->assertFalse($date1->equals($date3));
    }

    public function testThrowsExceptionWhenEndDateEqualsStartDate(): void
    {
        $sameDate = new DateTimeImmutable('tomorrow');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La fecha de fin debe ser posterior a la fecha de inicio');
        
        new ContractDate($sameDate, $sameDate);
    }
} 