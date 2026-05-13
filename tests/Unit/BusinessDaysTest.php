<?php

namespace Tests\Unit;

use App\Support\BusinessDays;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BusinessDaysTest extends TestCase
{
    public function test_adds_business_days_skipping_weekends(): void
    {
        $friday = Carbon::parse('2026-05-15 09:00'); // viernes

        $this->assertSame('2026-05-18', BusinessDays::addBusinessDays($friday, 1)->toDateString());
        $this->assertSame('2026-05-22', BusinessDays::addBusinessDays($friday, 5)->toDateString());
    }

    public function test_handles_starting_on_weekend(): void
    {
        $saturday = Carbon::parse('2026-05-16 09:00');

        // 1 día desde sábado → lunes (avanza primer día, salta domingo)
        $this->assertSame('2026-05-18', BusinessDays::addBusinessDays($saturday, 1)->toDateString());
    }

    public function test_identifies_business_days_correctly(): void
    {
        $this->assertTrue(BusinessDays::isBusinessDay(Carbon::parse('2026-05-15')));  // viernes
        $this->assertFalse(BusinessDays::isBusinessDay(Carbon::parse('2026-05-16'))); // sábado
        $this->assertFalse(BusinessDays::isBusinessDay(Carbon::parse('2026-05-17'))); // domingo
        $this->assertTrue(BusinessDays::isBusinessDay(Carbon::parse('2026-05-18')));  // lunes
    }

    public function test_counts_business_days_between_two_dates(): void
    {
        // lunes 11 → viernes 15 = 5 días hábiles (inclusivos)
        $this->assertSame(
            5,
            BusinessDays::between(Carbon::parse('2026-05-11'), Carbon::parse('2026-05-15'))
        );

        // viernes 15 → lunes 18 = 2 (viernes + lunes, sábado y domingo no cuentan)
        $this->assertSame(
            2,
            BusinessDays::between(Carbon::parse('2026-05-15'), Carbon::parse('2026-05-18'))
        );
    }
}
