<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Mvenghaus\PanelPermissions\Facades\Services\LazyPolicyService;
use Mvenghaus\PanelPermissions\Facades\Services\ModelService;
use Mvenghaus\PanelPermissions\Facades\Services\PolicyService;
use Mvenghaus\PanelPermissions\Filament\Resources\RoleResource;

class FilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-panel-permissions';
    }

    public function register(Panel $panel): void
    {
        Auth::setDefaultDriver(config('filament-panel-permissions.guard'));

        $panel
            ->authGuard(config('filament-panel-permissions.guard'))
            ->resources([
                RoleResource::class
            ]);

        Gate::before(
            fn($user, $ability) => $user->hasRole(config('filament-panel-permissions.super_admin_role')) ?: null
        );
    }

    public function boot(Panel $panel): void
    {
        $this->registerPolicies($panel);
    }

    public static function make(): static
    {
        return app(static::class);
    }

    private function registerPolicies(Panel $panel): void
    {
        foreach ($panel->getResources() as $resource) {
            $modelFQCN = (new $resource)->getModel();

            if (ModelService::isVendor($modelFQCN) &&
                LazyPolicyService::hasFile($modelFQCN)
            ) {
                Gate::policy($modelFQCN, LazyPolicyService::getFQCN($modelFQCN));
                continue;
            }

            if (PolicyService::hasFile($modelFQCN)) {
                Gate::policy($modelFQCN, PolicyService::getFQCN($modelFQCN));
            }
        }
    }
}
