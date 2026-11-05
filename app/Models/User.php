<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

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
        ];
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole(RoleName::Administrator->value);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->email_verified_at !== null
            && $this->can(PermissionName::AccessPanel->value);
    }

    public function recordedTrees(): HasMany
    {
        return $this->hasMany(Tree::class, 'recorded_by');
    }

    public function verifiedTrees(): HasMany
    {
        return $this->hasMany(Tree::class, 'verified_by');
    }

    public function monitorings(): HasMany
    {
        return $this->hasMany(TreeMonitoring::class, 'recorded_by');
    }

    public function verificationDecisions(): HasMany
    {
        return $this->hasMany(TreeVerification::class, 'verifier_id');
    }

    public function reviewedSurveyPoints(): HasMany
    {
        return $this->hasMany(TreeSurveyPoint::class, 'reviewed_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
