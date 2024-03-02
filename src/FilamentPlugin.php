<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\Gate;
use Mvenghaus\PanelPermissions\Facades\Services\LazyPolicyService;
use Mvenghaus\PanelPermissions\Facades\Services\ModelService;
use Mvenghaus\PanelPermissions\Facades\Services\PolicyService;

class FilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-panel-permissions';
    }

    public function register(Panel $panel): void
    {
        $panel->authGuard(config('filament-panel-permissions.guard'));

        Gate::before(fn($user, $ability) => $user->hasRole(config('filament-panel-permissions.super_admin_role')) ?: null);

        $this->registerPolicies($panel);
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

    public function boot(Panel $panel): void
    {
    }

    public static function make(): static
    {
        return app(static::class);
    }
}

