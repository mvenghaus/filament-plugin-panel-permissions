<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSuperAdminCommand extends Command
{
    public $signature = 'permissions:role:super-admin
        {--user_id= : ID of user to be made super admin.}
    ';

    public function handle(): int
    {
        $role = Role::firstOrCreate([
            'name' => config('filament-panel-permissions.super_admin_role'),
            'guard_name' => config('filament-panel-permissions.guard')
        ]);

        if (!empty($this->option('user_id'))) {
            $user = User::findOrFail($this->option('user_id'));
            $user->assignRole($role);
        }

        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        return 0;
    }
}