<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_of_form_id',
        'student_code',
        'status',

    ];

    public function values()
    {
        return $this->hasMany(FormRequestValue::class);
    }

    public function formType()
    {
        return $this->belongsTo(TypeOfForm::class, 'type_of_form_id');
    }
    public function requestStudents()
    {
        return $this->hasMany(RequestStudent::class);
    }
}
