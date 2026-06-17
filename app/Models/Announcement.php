<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'target_role',
        'target_district_id',
        'target_taluka_id',
        'target_village_id',
        'sender_id',
        'attachment_path',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function targetDistrict()
    {
        return $this->belongsTo(District::class, 'target_district_id');
    }

    public function targetTaluka()
    {
        return $this->belongsTo(Taluka::class, 'target_taluka_id');
    }

    public function targetVillage()
    {
        return $this->belongsTo(Village::class, 'target_village_id');
    }
}
