<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'program',
        'year_level',
    ];

    public function offerings(): HasMany
    {
        return $this->hasMany(SubjectOffering::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'section_id');
    }
}
