<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rskrf extends Model
{
    use HasFactory;
    protected $table = 'rskrf';
    protected $fillable = ['name', 'link', 'rating', 'barcode', 'research_date'];
}
