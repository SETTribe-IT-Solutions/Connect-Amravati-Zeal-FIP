<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = ['name'];

    public function talukas()
    {
        return $this->hasMany(Taluka::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
