<?php

namespace TeamSneakymouse\SneakyServerGroups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServerGroup extends Model
{
    protected $table = 'sneaky_server_groups';

    protected $fillable = [
        'name',
        'sort',
    ];

    protected $casts = [
        'sort' => 'integer',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(ServerGroupMembership::class, 'server_group_id');
    }
}
