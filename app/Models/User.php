<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Models\Branch;
use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'profile_img',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
        ];
    }


    public function moduleAssignments()
    {
        return $this->hasMany(ModuleStaff::class);
    }

    public function branchAssignments()
    {
        return $this->hasMany(BranchStaff::class);
    }

    public function modules()
    {
        return $this->belongsToMany(
            Module::class,
            'module_staff'
        )->withPivot([
            'assigned_at',
            'assigned_by',
            'is_active',
        ])->withTimestamps();
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_staff')
            ->withPivot(['assigned_at', 'assigned_by', 'is_active'])
            ->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function isBranchManager(): bool
    {
        return $this->hasRole('Branch Manager');
    }

    public function isInventoryManager(): bool
    {
        return $this->hasRole('Inventory Manager');
    }

    public function accessibleBranches(): Builder
    {
        if ($this->isSuperAdmin()) {
            return Branch::query()->where('is_active', true)->orderBy('id');
        }

        return Branch::query()
            ->where('branches.is_active', true)
            ->whereHas('staff', fn (Builder $query) => $query
                ->where('users.id', $this->id)
                ->where('branch_staff.is_active', true))
            ->orderBy('branches.id');
    }

    public function accessibleModules(?int $branchId = null, ?string $type = null): Builder
    {
        $query = Module::query()
            ->where('modules.is_active', true)
            ->when($branchId, fn (Builder $query) => $query->where('modules.branch_id', $branchId))
            ->when($type, fn (Builder $query) => $query->where('modules.type', $type));

        if ($this->isSuperAdmin()) {
            return $query->orderBy('modules.name');
        }

        $branchIds = $this->accessibleBranches()->pluck('branches.id');

        $query->whereIn('modules.branch_id', $branchIds);

        if ($this->isBranchManager() || $this->isInventoryManager()) {
            return $query->orderBy('modules.name');
        }

        return $query
            ->whereHas('staff', fn (Builder $query) => $query
                ->where('users.id', $this->id)
                ->where('module_staff.is_active', true))
            ->orderBy('modules.name');
    }

    public function canAccessBranch(mixed $branchId): bool
    {
        if (! $branchId) {
            return false;
        }

        return $this->isSuperAdmin()
            || $this->accessibleBranches()->where('branches.id', $branchId)->exists();
    }

    public function canAccessModule(mixed $moduleId, mixed $branchId = null): bool
    {
        if (! $moduleId) {
            return false;
        }

        $module = Module::query()->find($moduleId);

        if (! $module) {
            return false;
        }

        if ($branchId && (int) $module->branch_id !== (int) $branchId) {
            return false;
        }

        return $this->accessibleModules($module->branch_id)->where('modules.id', $module->id)->exists();
    }

    public function currentBranch(): ?Branch
    {
        $branchId = session('branch_id');

        if ($branchId && $this->canAccessBranch($branchId)) {
            return Branch::find($branchId);
        }

        return $this->accessibleBranches()->first();
    }

}
