<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ClassSession extends Model
{
    use HasFactory;

    public const PROVIDER_JITSI = 'jitsi';
    public const PROVIDER_EXTERNAL = 'external';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_LIVE = 'live';
    public const STATUS_ENDED = 'ended';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'provider',
        'meeting_url',
        'room_code',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if (empty($session->room_code)) {
                $session->room_code = Str::random(16);
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ClassAttendance::class, 'session_id');
    }

    public function isLive(): bool
    {
        return $this->status === self::STATUS_LIVE;
    }

    public function isEnded(): bool
    {
        return $this->status === self::STATUS_ENDED;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isJitsi(): bool
    {
        return $this->provider === self::PROVIDER_JITSI;
    }

    public function jitsiRoomName(): string
    {
        return 'lms-' . $this->room_code;
    }

    /**
     * Check if a student can join: live OR within 15 minutes before start, and not ended.
     */
    public function canStudentJoin(): bool
    {
        if ($this->isEnded()) {
            return false;
        }

        if ($this->isLive()) {
            return true;
        }

        // Within 15 minutes before starts_at
        return now()->gte($this->starts_at->copy()->subMinutes(15));
    }
}
