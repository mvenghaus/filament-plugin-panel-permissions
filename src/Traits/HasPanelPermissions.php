<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Traits;

use Filament\Panel;
use Spatie\Permission\Contracts\Role;

trait HasPanelPermissions
{
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool)$this->roles
            ->filter(fn(Role $role) => $role->guard_name === config('filament-panel-permissions.guard'))
            ->count();
    }
}