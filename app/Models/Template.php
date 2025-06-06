<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    public function items()
    {
        return $this->hasMany(TemplateItem::class);
    }

        public function templateID()
    {
        return $this->belongsTo(User::class);
    }

}
