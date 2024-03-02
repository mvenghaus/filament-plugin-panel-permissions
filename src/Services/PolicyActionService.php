<?php

namespace Mvenghaus\PanelPermissions\Services;

use Illuminate\Support\Collection;
use ReflectionObject;
use Mvenghaus\PanelPermissions\Facades\Services\PolicyService;

class PolicyActionService
{
    public function determinePolicyActions(string $policyFQCN): Collection
    {
        $reflectionModel = new ReflectionObject(new $policyFQCN);

        $defaultPolicyActions = PolicyService::getDefaultActions();

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