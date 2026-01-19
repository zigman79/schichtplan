<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Feiertag
 *
 * @property string $datum
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|Feiertag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feiertag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feiertag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Feiertag whereDatum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feiertag whereName($value)
 * @mixin \Eloquent
 */
class Feiertag extends Model
{
    use HasFactory;

    protected $table = 'feiertage';

    protected $primaryKey = 'datum';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['datum', 'name'];
}
