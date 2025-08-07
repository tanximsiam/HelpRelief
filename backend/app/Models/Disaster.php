<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'disaster_type',
        'location',
        'start_date',
        'status',
        'description',
        'created_by',
    ];

    // If disaster is related to a user (admin or NGO)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
