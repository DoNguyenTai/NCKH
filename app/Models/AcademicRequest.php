<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicRequest extends Model
{
    use HasFactory;
    protected $fillable = ['student_id', 'request_type', 'custom_fields'];
    protected $casts = [
        'custom_fields' => 'array', // Chuyển JSON thành mảng
    ];
}
