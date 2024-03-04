<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mvenghaus\PanelPermissions\Facades\Services\PolicyActionService;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function getName(string $action, string $modelFQCN): string
    {
        return Str::of($this->getModelPrefix($modelFQCN))
            ->append('.'.$action)
            ->toString();
    }

    public function translateName(string $permissionName): string
    {
        $guesses = [
            $permissionName,
            Str::of('filament-panel-permissions::translations.permission.action.')
                ->append(Str::of($permissionName)->explode('.')->last())
                ->toString()
        ];

        foreach ($guesses as $guess) {
            $translation = (string) __($guess);
            if ($guess !== $translation) {
                return $translation;
            }
        }

        return $permissionName;
    }

    public function getModelPrefix(string $modelFQCN): string
    {
        return Str::of($modelFQCN)
            ->ltrim("\\")
            ->replace("\\", "-")
            ->lower()
            ->toString();
    }

    public function getOptions(string $modelFQCN): Collection
    {
        $permissionNames = PolicyActionService::get($modelFQCN)
            ->map(fn(string $action) => $this->getName($action, $modelFQCN));

        return Permission::whereIn('name', $permissionNames)
            ->pluck('name', 'id')
            ->map(fn(string $name) => $this->translateName($name));
    }
}