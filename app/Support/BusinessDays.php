<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Cálculo de días hábiles. Lunes a Viernes. Sin feriados (decisión
 * 2026-05-13 — Sprint 3.6 #32). Si en el futuro necesitamos calendario
 * de feriados, agregar tabla `holidays` y consultarla en `isBusinessDay`.
 */
class BusinessDays
{
    /**
     * Suma N días hábiles a la fecha dada.
     *
     * Ejemplos (asumiendo $start = viernes 09:00):
     *  - addBusinessDays($start, 1) → lunes 09:00
     *  - addBusinessDays($start, 3) → miércoles 09:00
     *  - addBusinessDays($start, 5) → próximo viernes 09:00
     *
     * Si $start cae en fin de semana, la cuenta arranca el siguiente
     * lunes a la misma hora.
     */
    public static function addBusinessDays(CarbonInterface $start, int $days): Carbon
    {
        $date = Carbon::parse($start);
        $remaining = $days;

        while ($remaining > 0) {
            $date->addDay();
            if (self::isBusinessDay($date)) {
                $remaining--;
            }
        }

        return $date;
    }

    /**
     * True si la fecha es lunes a viernes.
     */
    public static function isBusinessDay(CarbonInterface $date): bool
    {
        return ! in_array($date->dayOfWeek, [CarbonInterface::SATURDAY, CarbonInterface::SUNDAY], true);
    }

    /**
     * Cuenta cuántos días hábiles hay entre dos fechas (inclusivos en
     * ambos extremos si ambos son hábiles). Útil para "X días desde el pago".
     */
    public static function between(CarbonInterface $from, CarbonInterface $to): int
    {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        $count = 0;
        $cursor = $start->copy();
        while ($cursor->lessThanOrEqualTo($end)) {
            if (self::isBusinessDay($cursor)) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }
}
