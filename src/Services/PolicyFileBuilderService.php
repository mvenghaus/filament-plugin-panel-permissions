<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Services;

use Illuminate\Support\Facades\File;
use Mvenghaus\PanelPermissions\Facades\Services\PolicyService;
use Mvenghaus\PanelPermissions\Facades\Services\PolicyActionService;
use Mvenghaus\PanelPermissions\Facades\Services\PermissionService;

class PolicyFileBuilderService
{
    public function build(string $modelFQCN): string
    {
        $policyContent = File::get(__DIR__."/../../stubs/Policy.stub");

        $modelFQCN = "\\".$modelFQCN;
        $modelName = class_basename($modelFQCN);
        $modelVariable = 'model';

        return strtr(
            $policyContent,
            collect([
                'namespace' => PolicyService::getNamespace($modelFQCN),
                'use authModelFQCN' => ($modelFQCN === PolicyService::getAuthModelFQCN() ? "" : sprintf("use %s;", ltrim(PolicyService::getAuthModelFQCN(), "\\"))),
                'use modelFQCN' => sprintf("use %s;", trim($modelFQCN, "\\")),
                'policyClassName' => ltrim(PolicyService::getClassName($modelFQCN), "\\"),
                'authModelName' => PolicyService::getAuthModelName(),
                'authModelVariable' => PolicyService::getAuthModelVariable(),
                'modelName' => $modelName,
                'modelVariable' => $modelVariable,
                ...PolicyActionService::getDefault()
                    ->mapWithKeys(fn(string $action) => [$action => PermissionService::getName($action, $modelFQCN)])
            ])
                ->mapWithKeys(fn(string $value, string $key) => ["{{ {$key} }}" => $value])
                ->all()
        );
    }
}