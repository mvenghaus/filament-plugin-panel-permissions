<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Services;

use Illuminate\Support\Str;

class PermissionService
{
    public function getActionName(string $action, string $modelFQCN): string
    {
        return Str::of($this->getModelPrefix($modelFQCN))
            ->append("::".$action)
            ->toString();
    }

    public function getModelPrefix(string $modelFQCN): string
    {
        return Str::of($modelFQCN)
            ->ltrim("\\")
            ->replace("\\", "-")
            ->lower()
            ->toString();
    }
}