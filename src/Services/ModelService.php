<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Services;

use Illuminate\Support\Str;
use ReflectionClass;

class ModelService
{
    public function isVendor(string $modelFQCN): bool
    {
        return Str::of((new ReflectionClass($modelFQCN))->getFileName())
            ->contains('vendor/');
    }

}