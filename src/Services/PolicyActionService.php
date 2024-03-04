<?php

namespace Mvenghaus\PanelPermissions\Services;

use ReflectionObject;
use Illuminate\Support\Collection;
use Mvenghaus\PanelPermissions\Facades\Services\LazyPolicyService;

class PolicyActionService
{
    public function get(string $modelFQCN): Collection
    {
        if (LazyPolicyService::hasFile($modelFQCN)) {
            return $this->determinePolicyActions(LazyPolicyService::getFQCN($modelFQCN));
        }

        return $this->getDefault();
    }

    public function getDefault(): Collection
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

    public function determinePolicyActions(string $policyFQCN): Collection
    {
        $reflectionModel = new ReflectionObject(new $policyFQCN);

        $defaultPolicyActions = $this->getDefault();

        $policyActions = collect();
        foreach ($reflectionModel->getMethods() as $method) {
            if ($defaultPolicyActions->contains($method->getName())) {
                $policyActions->add($method->getName());
            }

            if (!empty($method->getDocComment())) {
                preg_match('/@policyAction ([^ ]+)/i', $method->getDocComment(), $result);

                $policyAction = $result[1] ?? null;
                if($policyAction === null) {
                    continue;
                }

                $policyActions->add($policyAction);
            }
        }

        return $policyActions;
    }
}