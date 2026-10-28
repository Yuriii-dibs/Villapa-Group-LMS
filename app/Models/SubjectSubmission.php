<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_assignment_id',
        'user_id',
        'submission_type',
        'file_path',
        'file_name',
        'link_url',
        'content_text',
        'notes',
        'status',
        'submitted_at',
        'grade',
        'feedback',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(SubjectAssignment::class, 'subject_assignment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
