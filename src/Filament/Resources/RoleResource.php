<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Mvenghaus\PanelPermissions\Facades\Services\PermissionService;
use Mvenghaus\PanelPermissions\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    public static function form(Form $form): Form
    {
        return $form
            ->disabled(
                fn(?Role $role) => $role?->name === config('filament-panel-permissions.super_admin_role')
            )
            ->schema([
                ...static::getFormGeneralSchema(),
                ...static::getFormPermissionsSchema()
            ]);
    }

    public static function getFormGeneralSchema(): array
    {
        return [
            Forms\Components\Grid::make()
                ->schema([
                    Section::make(__('filament-panel-permissions::translations.section.general'))
                        ->schema([
                            TextInput::make('name')
                                ->label(__('filament-panel-permissions::translations.role.label.single'))
                                ->required()
                                ->maxLength(255),
                            Select::make('guard_name')
                                ->label(__('filament-panel-permissions::translations.guard_name'))
                                ->default(config('filament-panel-permissions.guard_name'))
                                ->options(
                                    fn() => collect(config('auth.guards') ?? [])
                                        ->filter(fn(array $data, string $guard) => $guard === 'filament')
                                        ->mapWithKeys(fn(array $data, string $guard) => [$guard => $guard])
                                )
                                ->required()
                        ])
                        ->columns()
                ])
        ];
    }

    public static function getFormPermissionsSchema(): array
    {
        $clusterGroups = collect(Filament::getCurrentPanel()->getResources())
            ->mapToGroups(function (string $resource) {
                $cluster = $resource::getCluster();
                $clusterDefaultName = __('filament-panel-permissions::translations.cluster_default_name');
                $clusterName = class_basename($cluster);

                return [
                    ($cluster ? $clusterName : $clusterDefaultName) => $resource
                ];
            });

        return [
            CheckboxList::make('permissions')
                ->relationship('permissions', 'name')
                ->saveRelationshipsWhenHidden()
                ->hidden(),
            Section::make(__('filament-panel-permissions::translations.section.permissions'))
                ->hidden(fn(?Role $role) => $role->getKey() === null)
                ->schema(
                    $clusterGroups
                        ->map(function (Collection $resources, string $clusterName) {
                            return Section::make($clusterName)
                                ->columns(4)
                                ->schema(
                                    $resources
                                        ->map(fn(string $resource
                                        ) => static::getFormPermissionResourceSchema($resource))
                                        ->all()
                                );
                        })
                        ->all()
                )
        ];
    }

    public static function getFormPermissionResourceSchema(string $resource): Component
    {
        $id = Str::of($resource)->slug()->toString();
        $modelLabel = $resource::getModelLabel();
        $permissionOptions = PermissionService::getOptions($resource::getModel());
        $permissionIds = $permissionOptions->keys();

        return Section::make()
            ->statePath($id)
            ->columnSpan(1)
            ->schema([
                Toggle::make('toggle')
                    ->label(new HtmlString("<span class='font-semibold'>{$modelLabel}</span>"))
                    ->live()
                    ->afterStateHydrated(function(Component $component, Role $role) use ($permissionIds) {
                        if ($role->name === config('filament-panel-permissions.super_admin_role')) {
                            $component->state(true);
                            return;
                        }

                        $rolePermissionIds = $role->permissions
                            ->pluck('id');

                        $selectedPermissionIds = collect($permissionIds)
                            ->filter(fn(int $permissionId) => $rolePermissionIds->contains($permissionId))
                            ->all();

                        $component->state(count($selectedPermissionIds) === count($permissionIds));
                    })
                    ->afterStateUpdated(function(Component $component, Get $get, Set $set) use ($permissionIds) {
                        $selectedPermissionIds = [];
                        if ($get('toggle')) {
                            $selectedPermissionIds = $permissionIds->all();
                        }

                        $rolePermissionIds = collect($get('../permissions'))
                            ->map(fn(string $rolePermissionId) => (int) $rolePermissionId)
                            ->filter(fn(int $rolePermissionId) => !$permissionIds->contains($rolePermissionId))
                            ->push(...$selectedPermissionIds)
                            ->values()
                            ->all();

                        $set('selected_permission_ids', $selectedPermissionIds);
                        $set('../permissions', $rolePermissionIds);
                    })
                ,
                Section::make('section')
                    ->id(fn(Role $role) => sprintf('%d#%s', $role->id, $id))
                    ->heading('')
                    ->description(function (Component $component) use ($id, $permissionOptions) {
                        $countSelected = count($component->getState()['selected_permission_ids']);

                        return new HtmlString(
                            sprintf(
                                '%s (%s/%d)',
                                __('filament-panel-permissions::translations.permissions'),
                                ($countSelected > 0 ? '<b>' . $countSelected . '</b>' : $countSelected),
                                count($permissionOptions)
                            )
                        );
                    })
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->schema([
                        CheckboxList::make('selected_permission_ids')
                            ->label('')
                            ->options($permissionOptions)
                            ->live()
                            ->afterStateHydrated(function (Component $component, Role $role) use ($permissionIds) {
                                $rolePermissionIds = $role->permissions
                                    ->pluck('id');

                                $selectedPermissionIds = collect($permissionIds)
                                    ->filter(fn(int $permissionId) => $rolePermissionIds->contains($permissionId))
                                    ->values()
                                    ->all();

                                $component->state($selectedPermissionIds);
                            })
                            ->afterStateUpdated(function(array $state, Get $get, Set $set) use ($permissionIds) {
                                $selectedPermissionIds = collect($state)
                                    ->map(fn(string $selectedPermissionId) => (int) $selectedPermissionId)
                                    ->values()
                                    ->all();

                                $rolePermissionIds = collect($get('../permissions'))
                                    ->map(fn(string $rolePermissionId) => (int) $rolePermissionId)
                                    ->filter(fn(int $rolePermissionId) => !$permissionIds->contains($rolePermissionId))
                                    ->push(...$selectedPermissionIds)
                                    ->values()
                                    ->all();

                                $set('toggle', (count($selectedPermissionIds) === count($permissionIds)));
                                $set('selected_permission_ids', $selectedPermissionIds);
                                $set('../permissions', $rolePermissionIds);
                            })
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->badge()
                    ->label(__('filament-panel-permissions::translations.role.label.single'))
                    ->colors(['primary'])
                    ->searchable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->badge()
                    ->label(__('filament-panel-permissions::translations.guard_name')),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->badge()
                    ->label(__('filament-panel-permissions::translations.permissions'))
                    ->counts('permissions')
                    ->colors(['success']),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament-panel-permissions::translations.updated_at'))
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament-panel-permissions::translations.role.label.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('filament-panel-permissions::translations.role.label.plural');
    }

}
