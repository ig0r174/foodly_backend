<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RskrfResearch extends Model
{
    use HasFactory;
    protected $table = 'rskrf_research';
    public $timestamps = false;
    protected $fillable = ['rskrf_id', 'name', 'value'];
}
