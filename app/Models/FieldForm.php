<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FieldForm extends Model
{
    use HasFactory;
    protected $fillable = [
        'form_id',
        'data_type',
        'value',
        'order',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($fieldForm) {
            $maxOrder = FieldForm::where('form_id', $fieldForm->form_id)->max('order');
            $fieldForm->order = $maxOrder + 1;
        });
    }
    


}
