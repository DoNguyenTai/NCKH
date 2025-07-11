<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DependencyForm extends Model
{
    use HasFactory;
    protected $fillable = [
        'form_id',
        'dependency_form_id',
    ];

     public function formType()
    {
        return $this->belongsTo(TypeOfForm::class, 'form_id');
    }
    public function dependencyName()
{
    return $this->belongsTo(TypeOfForm::class, 'dependency_form_id');
}
}
