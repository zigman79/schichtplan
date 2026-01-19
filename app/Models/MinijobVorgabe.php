<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MinijobVorgabe
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $year
 * @property string $month
 * @property float $hours
 * @property float $away
 * @method static \Illuminate\Database\Eloquent\Builder|MinijobVorgabe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MinijobVorgabe newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MinijobVorgabe query()
 * @method static \Illuminate\Database\Eloquent\Builder|MinijobVorgabe whereAway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MinijobVorgabe whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MinijobVorgabe whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MinijobVorgabe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MinijobVorgabe whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MinijobVorgabe whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MinijobVorgabe whereYear($value)
 * @mixin \Eloquent
 */
class MinijobVorgabe extends Model
{
    use HasFactory;

    protected $table = 'minijob_vorgaben';

    protected $fillable = [
        'year',
        'month',
        'hours',
        'away',
        'group_id'
    ];

}
