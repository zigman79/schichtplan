<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MinijobGroup extends Model
{
    use HasFactory;

    public $fillable = [
        'name',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'minijobgroup_users_pivot', 'group_id', 'user_id');
    }

    public function vorgaben(): HasMany
    {
        return $this->hasMany(MinijobVorgabe::class, 'group_id', 'id');
    }

    public static function withVorgaben(int $year, ?int $month = null): \Illuminate\Database\Eloquent\Collection
    {
        return self::with(['vorgaben' => function ($query) use ($year, $month) {
            $query->where('year', $year);
            if ($month) {
                $query->where('month', $month);
            }
        }])->get();
    }

    /**
     * On Delete remove all vorgaben, and set users to null
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($group) {
            $group->vorgaben()->delete();
            $group->users()->detach();
        });
    }
}
