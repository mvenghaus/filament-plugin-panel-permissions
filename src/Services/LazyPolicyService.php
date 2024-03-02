<?php

namespace Mvenghaus\PanelPermissions\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionObject;

class LazyPolicyService
{
    public function getFQCN(string $modelFQCN): string
    {
        return collect(explode("\\", $modelFQCN))
            ->slice(0, -2)
            ->add('Policies')
            ->add((new ReflectionObject(new $modelFQCN))->getShortName() . 'LazyPolicy')
            ->join("\\");
    }

    public function getFilePath(string $modelFQCN): string
    {
        $reflectionModel = new ReflectionObject(new $modelFQCN);

        return Str::of($reflectionModel->getFileName())
            ->dirname()
            ->append('/../Policies/')
            ->append($reflectionModel->getShortName())
            ->append('LazyPolicy.php')
            ->toString();
    }

    public function hasFile(string $modelFQCN): bool
    {
        return File::exists($this->getFilePath($modelFQCN));
    }
}