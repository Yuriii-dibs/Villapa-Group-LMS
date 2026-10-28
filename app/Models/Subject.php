<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_code',
        'title',
        'units',
        'program',
        'year_level',
        'section',
        'block',
        'instructor',
        'schedules',
        'fused_with',
        'meet_link',
        'description',
    ];

    protected $casts = [
        'schedules' => 'array',
        'units' => 'integer',
    ];

    public function offerings()
    {
        return $this->hasMany(SubjectOffering::class);
    }

    public function resources()
    {
        return $this->hasMany(SubjectResource::class);
    }

    public function assignments()
    {
        return $this->hasMany(SubjectAssignment::class);
    }

    /**
     * Resolve the registered User model matching this subject's assigned instructor name.
     */
    public function assignedInstructorUser(): ?User
    {
        if (empty($this->instructor)) {
            return null;
        }

        return User::whereHas('roles', fn($q) => $q->where('name', Role::INSTRUCTOR))
            ->where(function ($q) {
                $q->where('name', $this->instructor)
                  ->orWhere('name', 'like', '%' . $this->instructor . '%')
                  ->orWhereRaw('LOWER(name) = ?', [strtolower($this->instructor)]);
            })
            ->first();
    }

    /**
     * Get the resolved instructor user ID if present.
     */
    public function getAssignedInstructorIdAttribute(): ?int
    {
        return $this->assignedInstructorUser()?->id;
    }

    /**
     * Get the active Google Meet link for this subject.
     */
    public function getGoogleMeetUrlAttribute(): string
    {
        if (! empty($this->meet_link)) {
            return $this->meet_link;
        }

        // Clean slug code for standard Google Meet room
        $code = strtolower(preg_replace('/[^a-z0-9]/', '', $this->subject_code));
        return "https://meet.google.com/uep-{$code}-cls";
    }

    /**
     * Get a formatted string of all schedule slots.
     */
    public function scheduleLines(): array
    {
        return collect($this->schedules)->map(function ($slot) {
            $day  = $slot['day'] ?? '';
            $from = $slot['time_start'] ?? '';
            $to   = $slot['time_end'] ?? '';
            $type = $slot['type'] ?? '';
            $room = $slot['room'] ?? '';
            return "{$day} ({$from} - {$to}) – {$type}" . ($room ? " | {$room}" : '');
        })->all();
    }
}
