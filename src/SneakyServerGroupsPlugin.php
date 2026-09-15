<?php

namespace TeamSneakymouse\SneakyServerGroups;

use App\Contracts\Plugins\HasPluginSettings;
use App\Models\Server;
use Filament\Contracts\Plugin;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Panel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use TeamSneakymouse\SneakyServerGroups\Models\ServerGroup;
use TeamSneakymouse\SneakyServerGroups\Models\ServerGroupMembership;
use TeamSneakymouse\SneakyServerGroups\Providers\SneakyServerGroupsServiceProvider;
use Throwable;

class SneakyServerGroupsPlugin implements HasPluginSettings, Plugin
{
    public function getId(): string
    {
        return 'sneaky-server-groups';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}

    public function getServiceProviders(): array
    {
        return [
            SneakyServerGroupsServiceProvider::class,
        ];
    }

    public function getSettingsFormData(): array
    {
        return [
            'groups' => $this->hasTables() ? $this->loadGroups() : [],
        ];
    }

    public function getSettingsForm(): array
    {
        if (!$this->hasTables()) {
            return [
                \Filament\Forms\Components\Placeholder::make('migration_required')
                    ->label(fn () => trans('sneaky-server-groups::messages.migration_required'))
                    ->content(fn () => trans('sneaky-server-groups::messages.migration_required_message')),
            ];
        }

        return [
            Repeater::make('groups')
                ->label(fn () => trans('sneaky-server-groups::messages.server_groups'))
                ->helperText(fn () => trans('sneaky-server-groups::messages.server_groups_help'))
                ->schema([
                    Hidden::make('id'),
                    TextInput::make('name')
                        ->label(fn () => trans('sneaky-server-groups::messages.name'))
                        ->required()
                        ->maxLength(255),
                    Select::make('server_ids')
                        ->label(fn () => trans('sneaky-server-groups::messages.servers'))
                        ->multiple()
                        ->options(fn () => $this->serverOptions())
                        ->searchable()
                        ->preload()
                        ->reorderable()
                        ->helperText(fn () => trans('sneaky-server-groups::messages.servers_help'))
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                ])
                ->collapsed()
                ->reorderableWithButtons()
                ->addActionLabel(fn () => trans('sneaky-server-groups::messages.add_group')),
        ];
    }

    public function saveSettings(array $data): void
    {
        if (!$this->hasTables()) {
            Notification::make()
                ->title(trans('sneaky-server-groups::messages.migration_required'))
                ->body(trans('sneaky-server-groups::messages.migration_required_message'))
                ->danger()
                ->send();

            return;
        }

        $groups = collect($data['groups'] ?? []);
        $duplicateServerIds = $this->duplicateServerIds($groups);

        if ($duplicateServerIds !== []) {
            Notification::make()
                ->title(trans('sneaky-server-groups::messages.duplicate_servers'))
                ->body(trans('sneaky-server-groups::messages.duplicate_servers_message', [
                    'servers' => $this->serverNames($duplicateServerIds),
                ]))
                ->danger()
                ->send();

            return;
        }

        try {
            $validServerIds = Server::query()
                ->pluck('id')
                ->mapWithKeys(fn ($id) => [(int) $id => true]);

            DB::transaction(function () use ($groups, $validServerIds) {
                ServerGroupMembership::query()->delete();

                $keptGroupIds = [];

                foreach ($groups as $groupIndex => $groupData) {
                    $group = isset($groupData['id'])
                        ? ServerGroup::query()->whereKey($groupData['id'])->first()
                        : null;

                    if (!$group) {
                        $group = new ServerGroup();
                    }

                    $group->name = $groupData['name'] ?? trans('sneaky-server-groups::messages.default_group_name');
                    $group->sort = $groupIndex * 10;
                    $group->save();

                    $keptGroupIds[] = $group->id;

                    $rows = collect($groupData['server_ids'] ?? [])
                        ->map(fn ($serverId) => (int) $serverId)
                        ->unique()
                        ->filter(fn ($serverId) => isset($validServerIds[$serverId]))
                        ->values()
                        ->map(fn ($serverId, $position) => [
                            'server_group_id' => $group->id,
                            'server_id' => $serverId,
                            'position' => $position * 10,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ])
                        ->all();

                    if ($rows !== []) {
                        ServerGroupMembership::query()->insert($rows);
                    }
                }

                ServerGroup::query()
                    ->whereNotIn('id', $keptGroupIds)
                    ->delete();
            });
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title(trans('sneaky-server-groups::messages.settings_save_failed'))
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title(trans('sneaky-server-groups::messages.settings_saved'))
            ->success()
            ->send();
    }

    private function hasTables(): bool
    {
        return Schema::hasTable('sneaky_server_groups')
            && Schema::hasTable('sneaky_server_group_memberships');
    }

    private function loadGroups(): array
    {
        return ServerGroup::query()
            ->with('memberships')
            ->orderBy('sort')
            ->get()
            ->map(fn (ServerGroup $group) => [
                'id' => $group->id,
                'name' => $group->name,
                'server_ids' => $group->memberships
                    ->sortBy('position')
                    ->pluck('server_id')
                    ->all(),
            ])
            ->all();
    }

    private function serverOptions(): array
    {
        return Server::query()
            ->with('node')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (Server $server) {
                $node = $server->node?->name;
                $label = $node ? "{$server->name} ({$node})" : $server->name;

                return [$server->id => $label];
            })
            ->all();
    }

    private function duplicateServerIds($groups): array
    {
        $seen = [];
        $duplicates = [];

        foreach ($groups as $group) {
            foreach (($group['server_ids'] ?? []) as $serverId) {
                $serverId = (int) $serverId;

                if (isset($seen[$serverId])) {
                    $duplicates[] = $serverId;
                }

                $seen[$serverId] = true;
            }
        }

        return array_values(array_unique($duplicates));
    }

    private function serverNames(array $serverIds): string
    {
        return Server::query()
            ->whereIn('id', $serverIds)
            ->pluck('name')
            ->join(', ');
    }
}
