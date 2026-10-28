<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubjectAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'title',
        'description',
        'instructions',
        'due_date',
        'total_points',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(SubjectSubmission::class);
    }

    public function submissionFor(User $user): ?SubjectSubmission
    {
        return $this->submissions()->where('user_id', $user->id)->first();
    }
}
