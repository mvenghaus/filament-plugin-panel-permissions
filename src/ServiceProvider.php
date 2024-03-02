<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions;

use Mvenghaus\PanelPermissions\Commands\RoleSuperAdminCommand;
use Mvenghaus\PanelPermissions\Commands\SyncCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-panel-permissions';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasCommands([
                RoleSuperAdminCommand::class,
                SyncCommand::class
            ]);
    }

    public function packageBooted()
    {

        parent::packageBooted();
    }
}
