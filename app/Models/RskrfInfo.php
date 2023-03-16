<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RskrfInfo extends Model
{
    use HasFactory;
    protected $table = 'rskrf_info';
    public $timestamps = false;
    protected $fillable = ['rskrf_id', 'type', 'value'];
}
