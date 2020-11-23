<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class X extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function ys()
    {
        return $this->hasMany('App\Models\Y');
    }
    public function zs()
    {
        return $this->belongsToMany('App\Models\Z');
    }
}
