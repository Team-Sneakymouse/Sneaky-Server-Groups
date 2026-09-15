<?php

namespace TeamSneakymouse\SneakyServerGroups\Models;

use App\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerGroupMembership extends Model
{
    protected $table = 'sneaky_server_group_memberships';

    protected $fillable = [
        'server_group_id',
        'server_id',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ServerGroup::class, 'server_group_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id');
    }
}
