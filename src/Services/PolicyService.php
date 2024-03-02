<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionObject;

class PolicyService
{
    public function getFilePath(string $modelFQCN): string
    {
        $reflectionModel = new ReflectionObject(new $modelFQCN);

        return Str::of(base_path('app/Policies/'))
            ->append($reflectionModel->getNamespaceName())
            ->replace('\\', '/')
            ->append("/")
            ->append($reflectionModel->getShortName())
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
            ->append((new ReflectionObject(new $modelFQCN))->getNamespaceName())
            ->toString();
    }

    public function getClassName(string $modelFQCN): string
    {
        return (new ReflectionObject(new $modelFQCN))->getShortName() . "Policy";
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
        $authModelFQCN = $this->getAuthModelFQCN();
        return (new ReflectionObject(new $authModelFQCN))->getShortName();
    }

    public function getAuthModelVariable(): string
    {
        return Str::of($this->getAuthModelName())
            ->lcfirst()
            ->toString();
    }

    public function getDefaultActions(): Collection
    {
        return collect([
            'viewAny',
            'view',
            'create',
            'update',
            'delete',
            'deleteAny',
            'forceDelete',
            'forceDeleteAny',
            'restore',
            'restoreAny',
            'replicate',
            'reorder'
        ]);
    }
}