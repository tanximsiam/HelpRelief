<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NgoInviteLink extends Model
{
    //use HasFactory;

    protected $fillable = [
        'ngo_id',
        'token',
        'privilege_role',
        'is_primary',
        'usage_limit',
        'used_count',
        'active',
    ];

    public function ngo()
    {
        return $this->belongsTo(Ngo::class, 'ngo_id');
    }
}
