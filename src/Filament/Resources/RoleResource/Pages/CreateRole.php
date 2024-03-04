<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Filament\Resources\RoleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Mvenghaus\PanelPermissions\Filament\Resources\RoleResource;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
}
