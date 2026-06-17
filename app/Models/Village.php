<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $fillable = ['taluka_id', 'name'];

    public function taluka()
    {
        return $this->belongsTo(Taluka::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
