<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'parent_id',
        'is_folder',
    ];
    public function forms()
    {
        return $this->hasMany(TypeOfForm::class, 'parent_id');
    }

     public function note()
    {
        return $this->hasMany(Note::class, 'parent_id');
    }

       public function resquestStuden()
    {
        return $this->hasMany(RequestStudent::class);
    }
}
