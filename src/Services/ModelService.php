<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Services;

use Illuminate\Support\Str;
use ReflectionObject;

class ModelService
{
    public function isVendor(string $modelFQCN): bool
    {
        return Str::of((new ReflectionObject(new $modelFQCN))->getFileName())
            ->contains('vendor/');
    }

}