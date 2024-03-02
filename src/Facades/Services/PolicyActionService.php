<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Facades\Services;

use Illuminate\Support\Facades\Facade;

/** @see \Mvenghaus\PanelPermissions\Services\PolicyActionService */
class PolicyActionService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mvenghaus\PanelPermissions\Services\PolicyActionService::class;
    }
}