<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\EvaluatorQueue;
use App\Filament\Widgets\EvaluatorTasksOverview;
use BackedEnum;
use Filament\Pages\Dashboard;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Roadmap #35 — escritorio (home) del rol evaluator.
 *
 * Dashboard dedicado con ruta propia (/admin/evaluator-desk) que NO colisiona
 * con el Dashboard genérico en '/'. El evaluador aterriza acá tras el login
 * (RedirectEvaluatorToDesk), no en el Dashboard de admin. Solo muestra sus
 * widgets scopeados; canAccess lo restringe al rol evaluator.
 */
class EvaluatorDesk extends Dashboard
{
    protected static string $routePath = '/evaluator-desk';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = -10;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->hasRole('evaluator') && ! $user->hasRole('super_admin'));
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.evaluation');
    }

    public static function getNavigationLabel(): string
    {
        return __('evaluator_desk.nav_label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('evaluator_desk.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('evaluator_desk.subtitle');
    }

    /**
     * Solo los widgets del evaluador — no los globales de admin.
     */
    public function getWidgets(): array
    {
        return [
            EvaluatorTasksOverview::class,
            EvaluatorQueue::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 1;
    }
}
