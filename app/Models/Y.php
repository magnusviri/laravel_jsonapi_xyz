<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Y extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'x_id'];

    public function xes()
    {
        return $this->belongsTo('App\Models\X');
    }
}
