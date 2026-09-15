<?php

namespace TeamSneakymouse\SneakyServerGroups\Providers;

use App\Filament\App\Resources\Servers\Pages\ListServers as AppListServers;
use App\Models\Server;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use TeamSneakymouse\SneakyServerGroups\Models\ServerGroupMembership;

class SneakyServerGroupsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Server::resolveRelationUsing('sneakyServerGroupMembership', function (Server $server) {
            return $server->hasOne(ServerGroupMembership::class, 'server_id', 'id')
                ->with('group');
        });
    }

    public function boot(): void
    {
        if (!Schema::hasTable('sneaky_server_groups') || !Schema::hasTable('sneaky_server_group_memberships')) {
            return;
        }

        Table::configureUsing(function (Table $table): void {
            $component = $table->getLivewire();

            if (!$component instanceof AppListServers) {
                return;
            }

            $table
                ->groups([
                    $this->makeServerGroup(),
                ])
                ->defaultGroup('sneakyServerGroupMembership.group.name');
        });
    }

    private function makeServerGroup(): Group
    {
        $group = Group::make('sneakyServerGroupMembership.group.name')
            ->label(fn () => trans('sneaky-server-groups::messages.server_groups'))
            ->titlePrefixedWithLabel(false);

        $group
            ->getTitleFromRecordUsing(function (Server $server): string {
                return $server->sneakyServerGroupMembership?->group?->name
                    ?? trans('sneaky-server-groups::messages.ungrouped');
            })
            ->collapsible()
            ->orderQueryUsing(function (Builder $query, string $direction): Builder {
                $serverIdColumn = Server::query()->qualifyColumn('id');
                $groupSortSub = "(SELECT ssg.sort FROM sneaky_server_group_memberships ssgm JOIN sneaky_server_groups ssg ON ssg.id = ssgm.server_group_id WHERE ssgm.server_id = {$serverIdColumn} LIMIT 1)";
                $positionSub = "(SELECT ssgm.position FROM sneaky_server_group_memberships ssgm WHERE ssgm.server_id = {$serverIdColumn} LIMIT 1)";
                $directionSql = $direction === 'desc' ? 'DESC' : 'ASC';

                return $query
                    ->orderByRaw("COALESCE({$groupSortSub}, 2147483647) {$directionSql}")
                    ->orderByRaw("COALESCE({$positionSub}, 2147483647) {$directionSql}");
            });

        return $group;
    }
}
