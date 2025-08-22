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
        'description',
        'website',
        'email',
        'phone',
        'based_in',
        'registration_no',
        'established_year',
        'director_name',
        'director_phone',
        'num_employees',
        'logo_url',
        'approved',
        ];

    public function staffs()
    {
        return $this->hasMany(NgoStaff::class);
    }

    public function domains()
    {
        return $this->hasMany(NgoEmailDomain::class);
    }

    public function causeFocuses()
    {
        return $this->belongsToMany(CauseFocus::class, 'ngo_cause_focuses');
    }
    public function aidRequests()
    {
        return $this->hasMany(AidRequest::class);
    }

    public function aidSupports()
    {
        return $this->hasMany(AidSupport::class);
    }

    public function volunteers()
    {
        return $this->hasMany(VolunteerRegistration::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'created_by'); // assuming created_by = ngo_id
    }

    public function disasterCampaignAssignments()
    {
        return $this->hasMany(DisasterCampaignAssignment::class);
    }
    
}
