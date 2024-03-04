<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class PolicyService
{
    public function getFilePath(string $modelFQCN): string
    {
        $reflectionClass = new ReflectionClass($modelFQCN);

        return Str::of(base_path('app/Policies/'))
            ->append($reflectionClass->getNamespaceName())
            ->replace('\\', '/')
            ->append("/")
            ->append($reflectionClass->getShortName())
            ->append('Policy.php')
            ->toString();
    }

    public function hasFile(string $modelFQCN): bool
    {
        return File::exists($this->getFilePath($modelFQCN));
    }

    public function getNamespace(string $modelFQCN): string
    {
        return Str::of("App\\Policies\\")
            ->append((new ReflectionClass($modelFQCN))->getNamespaceName())
            ->toString();
    }

    public function getClassName(string $modelFQCN): string
    {
        return class_basename($modelFQCN) . "Policy";
    }

    public function getFQCN(string $modelFQCN): string
    {
        return Str::of($this->getNamespace($modelFQCN))
            ->append("\\")
            ->append($this->getClassName($modelFQCN))
            ->toString();
    }

    public function getAuthModelFQCN(): string
    {
        return "\\App\\Models\\User";
    }

    public function getAuthModelName(): string
    {
        return class_basename($this->getAuthModelFQCN());
    }

    public function getAuthModelVariable(): string
    {
        return Str::of($this->getAuthModelName())
            ->lcfirst()
            ->toString();
    }
}