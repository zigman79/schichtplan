<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Relationship to Users
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_group_user', 'job_group_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Relationship to Shifts
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    /**
     * On Delete remove all shifts and detach users
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($group) {
            $group->shifts()->delete();
            $group->users()->detach();
        });
    }
}