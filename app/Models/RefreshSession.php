<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RefreshSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'id', 'user_id', 'jti', 'token_hash', 'family_id', 'expires_at', 'revoked_at', 'replaced_by_session_id',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
