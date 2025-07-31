<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CauseFocus extends Model
{
    //
    protected $fillable = [
        'name',
    ];

    public function ngos()
    {
        return $this->belongsToMany(Ngo::class, 'ngo_cause_focuses');
    }
}
