<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStudent extends Model
{
    use HasFactory;

    protected $fillable = ['folder_id', 'student_code','status'];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
    // Quan hệ tới FormRequest
    public function formRequest()
    {
        return $this->belongsTo(FormRequest::class, 'form_request_id');
    }

    // Quan hệ tới Student (dựa theo student_code)
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_code', 'student_code');
    }
    public function requestType()
    {
        return $this->belongsTo(RequestType::class);
    }
}
