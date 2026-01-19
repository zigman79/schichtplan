<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


/**
 * App\Models\Pause
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $arbeitszeit_id
 * @property Carbon $beginn
 * @property Carbon|null $ende
 * @property-read Arbeitszeit $arbeitszeit
 * @method static Builder|Pause newModelQuery()
 * @method static Builder|Pause newQuery()
 * @method static Builder|Pause query()
 * @method static Builder|Pause whereArbeitszeitId($value)
 * @method static Builder|Pause whereBeginn($value)
 * @method static Builder|Pause whereCreatedAt($value)
 * @method static Builder|Pause whereEnde($value)
 * @method static Builder|Pause whereId($value)
 * @method static Builder|Pause whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Pause extends Model
{
    use HasFactory;

    protected $table = 'pausen';

    protected $fillable = [
        'beginn',
        'ende',
        'arbeitszeit_id'
    ];

    /**
     * Relation between Pause and Arbeitszeit
     */
    public function arbeitszeit()
    {
        return $this->belongsTo('App\Models\Arbeitszeit');
    }

    public function calculatePausenzeit()
    {
        if ($this->ende == null) {
            return 0;
        }
        $start = strtotime($this->beginn);
        $end = strtotime($this->ende);
        return round(($end - $start) / 3600, 2);
    }
}
