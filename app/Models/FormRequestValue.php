<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormRequestValue extends Model
{
    protected $fillable = ['form_request_id', 'field_form_id', 'value'];

    use HasFactory;

    public function submission()
    {
        return $this->belongsTo(FormRequest::class);
    }

    public function field()
    {
        return $this->belongsTo(FieldForm::class, 'field_form_id');
    }
    protected $casts = [
        'value' => 'array', // Tự động decode khi lấy ra
    ];
}
