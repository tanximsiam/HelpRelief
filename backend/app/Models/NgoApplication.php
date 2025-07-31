<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NgoApplication extends Model
{
    //
    protected $fillable = [
        'organization',
        'contact_person',
        'designation',
        'email',
        'phone',
        'based_in',
        'description',
        'status',
    ];
}
