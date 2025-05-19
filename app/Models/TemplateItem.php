<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'template_id',
        'type',
        'left',
        'top',
        'width',
        'height',
        'class_name',
        'value',
        'data',
        'rows',
        'columns',
        'column_ratios',
        'nested_config',
    ];

    protected $casts = [
        'data' => 'array',
        'column_ratios' => 'array',
        'nested_config' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
