<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectOffering extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_term_id',
        'subject_id',
        'section_id',
        'instructor_id',
        'instructor_name',
        'schedules',
        'room',
        'meet_link',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'schedules' => 'array',
        ];
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'offering_enrollments')
            ->withPivot(['status', 'enrolled_at'])
            ->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SubjectAssignment::class, 'subject_id', 'subject_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'subject_offering_id');
    }

    public function getEffectiveMeetLinkAttribute(): string
    {
        if (!empty($this->meet_link)) {
            return $this->meet_link;
        }

        if ($this->subject && !empty($this->subject->meet_link)) {
            return $this->subject->meet_link;
        }

        $code = strtolower(preg_replace('/[^a-z0-9]/', '', $this->subject?->subject_code ?? 'cls'));
        return "https://meet.google.com/uep-{$code}-cls";
    }
}
