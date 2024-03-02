<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Facades\Services;

use Illuminate\Support\Facades\Facade;

/** @see \Mvenghaus\PanelPermissions\Services\ModelService */
class ModelService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mvenghaus\PanelPermissions\Services\ModelService::class;
    }
}