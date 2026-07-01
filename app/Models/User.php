<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasLocalePreference, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, HasPanelShield;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Locale para mailables/notifications: Laravel detecta esta interfaz y traduce
     * cada notification al idioma del destinatario, no del request que la dispara.
     */
    public function preferredLocale(): string
    {
        return $this->locale ?? config('app.locale', 'es');
    }

    /**
     * Roadmap #35 — override del default de HasPanelShield (que solo admite
     * super_admin o panel_user). Sin esto, asignar el rol `evaluator` desde
     * el selector de roles de UserResource (que hace sync, no append) puede
     * quitarle a un usuario el rol `panel_user` auto-asignado al crearse y
     * dejarlo con 403 al entrar a /admin, aunque tenga permisos de Shield.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super_admin', 'panel_user', 'evaluator']);
    }

    /**
     * Roadmap #35 — rol principal del usuario para decisiones de panel/branding
     * (badge de rol, acento del header, home del panel). Precedencia:
     * super_admin > evaluator > panel_user. Un editor común (sin rol Spatie)
     * devuelve null. Centraliza la lógica para no repetir hasRole() en las vistas.
     */
    public function primaryPanelRole(): ?string
    {
        if ($this->hasRole('super_admin')) {
            return 'super_admin';
        }

        if ($this->hasRole('evaluator')) {
            return 'evaluator';
        }

        if ($this->hasRole('panel_user')) {
            return 'panel_user';
        }

        return null;
    }
}
