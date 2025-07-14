<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
  use HasFactory;

  protected $fillable = [
    'student_code',
    'name',
    'email'
  ];


  public function user()
  {
    return $this->hasOne(Student::class);
  }

  public function request_student()
  {
    return $this->hasMany(RequestStudent::class);
  }

  public function requestType()
  {
    return $this->belongsTo(RequestType::class);
  }


  public function formRequestValues()
  {
    return $this->hasMany(FormRequestValue::class, 'student_code', 'student_code');
  }

  public function requestStudents()
    {
        return $this->hasMany(RequestStudent::class, 'student_code', 'student_code');
    }
}
