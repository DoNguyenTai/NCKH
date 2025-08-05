<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormRequestValue extends Model
{

    use HasFactory;
    protected $fillable = [
        'form_request_id',
        'field_form_id',
        'student_code',
        'value',
    ];
    public function submission()
    {
        return $this->belongsTo(FormRequest::class,'form_request_id');
    }

    public function field()
    {
        return $this->belongsTo(FieldForm::class, 'field_form_id');
    }
    protected $casts = [
        'value' => 'array', // Tự động decode khi lấy ra
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_code', 'student_code');
    }
}
