<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];
    public function requestStudents()
    {
        return $this->hasMany(RequestStudent::class);
    }
}
