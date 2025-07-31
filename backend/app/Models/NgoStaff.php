<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NgoStaff extends Model
{
    //
    protected $fillable = [
        'user_id',
        'ngo_id',
        'designation',
        'privilege_role',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ngo()
    {
        return $this->belongsTo(Ngo::class);
    }
}
