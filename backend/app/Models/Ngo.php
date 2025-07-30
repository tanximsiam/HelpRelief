<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ngo extends Model
{
    //
    protected $fillable = [
        'name',
        'rep_contact',
        'rep_designation',
        'rep_email',
        'rep_phone',
        'description',
        'website',
        'status',
        ];

    public function users()
    {        return $this->hasMany(User::class, 'ngo_id');
    }
}
