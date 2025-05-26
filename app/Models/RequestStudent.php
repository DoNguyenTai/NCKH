<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStudent extends Model
{
    use HasFactory;
    protected $fillable = [
        // 'reason',
        'start_date',
        'end_date',
        'status',
        // 'note'
    ];

    public function studentId()
    {
        return $this->belongsTo(Student::class);
    }
    public function requestType()
    {
        return $this->belongsTo(RequestType::class);
    }
}
