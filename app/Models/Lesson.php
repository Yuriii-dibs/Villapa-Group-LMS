<?php

namespace App\Models;

use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    public const TYPE_TEXT = 'text';
    public const TYPE_VIDEO = 'video';
    public const TYPE_PDF = 'pdf';
    public const TYPE_FILE = 'file';
    public const TYPE_EXTERNAL_LINK = 'external_link';

    protected $fillable = [
        'module_id',
        'title',
        'slug',
        'content_type',
        'body',
        'video_url',
        'file_path',
        'external_url',
        'duration_minutes',
        'order',
        'is_preview',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_preview' => 'boolean',
            'is_published' => 'boolean',
            'order' => 'integer',
            'duration_minutes' => 'integer',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LessonResource::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function usersCompleted(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lesson_progress')
            ->wherePivot('is_completed', true)
            ->withTimestamps();
    }
}
