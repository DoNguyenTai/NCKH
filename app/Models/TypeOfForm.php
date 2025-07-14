<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeOfForm extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'form_model',
        'parent_id',
    ];

    public function fieldForm()
    {
        return $this->hasMany(FieldForm::class, 'form_id');
    }

    public function formRequest()
    {
        return $this->hasMany(FormRequest::class, 'type_of_form_id');
    }

    public function dependencyForm()
    {
        return $this->hasMany(DependencyForm::class, 'form_id');
    }
    public function folder()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }
}
