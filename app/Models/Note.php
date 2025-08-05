<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;
      protected $fillable = [
        'content',
        'parent_id',
        'name',
        
    ];
      public function folder()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }
}
