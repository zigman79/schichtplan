<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_group_id',
        'shift_date',
        'start_time',
        'end_time',
        'admin_comment',
        'required_employees',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    protected $appends = [
        'available_slots',
        'enrolled_count',
        'is_full',
    ];

    /**
     * Relationship to JobGroup
     */
    public function jobGroup(): BelongsTo
    {
        return $this->belongsTo(JobGroup::class);
    }

    /**
     * Relationship to enrolled Users
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shift_user', 'shift_id', 'user_id')
            ->withPivot('user_comment')
            ->withTimestamps();
    }

    /**
     * Relationship to unavailable Users (Absagen)
     */
    public function unavailableUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shift_unavailability', 'shift_id', 'user_id')
            ->withPivot('reason')
            ->withTimestamps();
    }

    /**
     * Get the number of enrolled employees
     */
    public function getEnrolledCountAttribute(): int
    {
        return $this->users()->count();
    }

    /**
     * Get the number of available slots
     */
    public function getAvailableSlotsAttribute(): int
    {
        return max(0, $this->required_employees - $this->enrolled_count);
    }

    /**
     * Check if shift is full
     */
    public function getIsFullAttribute(): bool
    {
        return $this->enrolled_count >= $this->required_employees;
    }

    /**
     * Check if a user can enroll
     */
    public function canUserEnroll(User $user): bool
    {
        // Check if shift is full
        if ($this->is_full) {
            return false;
        }

        // Check if user is in the job group
        if (!$user->jobGroups->contains($this->job_group_id)) {
            return false;
        }

        // Check if user is already enrolled
        if ($this->users->contains($user->id)) {
            return false;
        }

        return true;
    }

    /**
     * On Delete detach all users
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($shift) {
            $shift->users()->detach();
            $shift->unavailableUsers()->detach();
        });
    }
}