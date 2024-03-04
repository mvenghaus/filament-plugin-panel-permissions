<?php

namespace Mvenghaus\PanelPermissions\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class LazyPolicyService
{
    public function getFQCN(string $modelFQCN): string
    {
        return collect(explode("\\", $modelFQCN))
            ->slice(0, -2)
            ->add('Policies')
            ->add(class_basename($modelFQCN) . 'LazyPolicy')
            ->join("\\");
    }

    public function getFilePath(string $modelFQCN): string
    {
        $reflectionClass = new ReflectionClass($modelFQCN);

        return Str::of($reflectionClass->getFileName())
            ->dirname()
            ->append('/../Policies/')
            ->append($reflectionClass->getShortName())
            ->append('LazyPolicy.php')
            ->toString();
    }

    public function hasFile(string $modelFQCN): bool
    {
        return File::exists($this->getFilePath($modelFQCN));
    }
}