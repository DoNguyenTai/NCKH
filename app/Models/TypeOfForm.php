<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeOfForm extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];

    public function fieldForm()
    {
        return $this->hasMany(FieldForm::class, 'form_id');
    }
}
