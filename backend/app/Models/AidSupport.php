<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AidSupport extends Model
{
    protected $table = 'aid_supports';

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'user_id', 
        'disaster_id', 
        'ngo_id',
        'aid_type', 
        'quantity', 
        'description',
        'contact', 
        'status'
    ];
}