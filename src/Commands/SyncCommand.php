<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Commands;

use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Mvenghaus\PanelPermissions\Facades\Services\FileService;
use Mvenghaus\PanelPermissions\Facades\Services\ModelService;
use Mvenghaus\PanelPermissions\Facades\Services\PermissionService;
use Mvenghaus\PanelPermissions\Facades\Services\PolicyActionService;
use Mvenghaus\PanelPermissions\Facades\Services\PolicyFileBuilderService;
use Mvenghaus\PanelPermissions\Facades\Services\PolicyService;
use Mvenghaus\PanelPermissions\Facades\Services\LazyPolicyService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SyncCommand extends Command
{
    public $signature = 'permissions:sync
        {--ignore-existing : Ignore existing policies?}
    ';

    public function handle(): int
    {
        foreach (Filament::getPanels() as $panel) {
            foreach ($panel->getResources() as $resource) {

                $modelFQCN = $resource::getModel();

                $this->generatePolicyFiles($modelFQCN);

                $this->generatePermissions($modelFQCN);
            }
        }

        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        return 0;
    }

    private function generatePolicyFiles(string $modelFQCN): void
    {
        if (ModelService::isVendor($modelFQCN) &&
            LazyPolicyService::hasFile($modelFQCN)
        ) {
            return;
        }

        $policyFilePath = PolicyService::getFilePath($modelFQCN);

        if (!empty($this->option('ignore-existing')) &&
            File::exists($policyFilePath)
        ) {
            return;
        }

        FileService::write($policyFilePath, PolicyFileBuilderService::build($modelFQCN));
    }

    private function generatePermissions(string $modelFQCN): void
    {
        PolicyActionService::get($modelFQCN)
            ->map(fn(string $action) => PermissionService::getName($action, $modelFQCN))
            ->each(function (string $name) {
                Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => config('filament-panel-permissions.guard')
                ]);
            });
    }
}