<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ngo extends Model
{
    use HasFactory;
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
