<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
  use HasFactory;




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
}
