<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;
    protected $fillable = ['student_id', 'title', 'message'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
