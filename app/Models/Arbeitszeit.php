<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\Arbeitszeit
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $user_id
 * @property string $tag
 * @property string|null $beginn
 * @property string|null $ende
 * @property string|null $frei_urlaub_krank
 * @property float|null $arbeitszeit
 * @property bool $beginn_bestaetigt
 * @property bool $ende_bestaetigt
 * @property int $pausenzeit
 * @property-read Collection|Pause[] $pausen
 * @property-read int|null $pausen_count
 * @property-read User $user
 * @method static Builder|Arbeitszeit newModelQuery()
 * @method static Builder|Arbeitszeit newQuery()
 * @method static Builder|Arbeitszeit query()
 * @method static Builder|Arbeitszeit whereArbeitszeit($value)
 * @method static Builder|Arbeitszeit whereBeginn($value)
 * @method static Builder|Arbeitszeit whereBeginnBestaetigt($value)
 * @method static Builder|Arbeitszeit whereCreatedAt($value)
 * @method static Builder|Arbeitszeit whereEnde($value)
 * @method static Builder|Arbeitszeit whereEndeBestaetigt($value)
 * @method static Builder|Arbeitszeit whereFreiUrlaubKrank($value)
 * @method static Builder|Arbeitszeit whereId($value)
 * @method static Builder|Arbeitszeit wherePausenzeit($value)
 * @method static Builder|Arbeitszeit whereTag($value)
 * @method static Builder|Arbeitszeit whereUpdatedAt($value)
 * @method static Builder|Arbeitszeit whereUserId($value)
 * @mixin Eloquent
 * @property-read mixed $arbeitszeit_in_minutes
 * @property-read mixed $readable_arbeitszeit
 * @property-read mixed $readable_future_time
 * @property-read mixed $readable_time
 */
class Arbeitszeit extends Model
{
    use HasFactory;

    protected $table = 'arbeitszeiten';

    protected $fillable = [
        'user_id',
        'tag',
        'beginn',
        'ende',
        'frei_urlaub_krank',
        'arbeitszeit',
        'beginn_bestaetigt',
        'ende_bestaetigt',
        'pausenzeit',
        'updated_at',
    ];

    private $optionLabels = [
        'frei' => 'Frei',
        'urlaub' => 'Urlaub',
        'krank' => 'Krank',
        'schule' => 'Schule'
    ];

    protected $appends = ['readable_time', 'readable_future_time'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($arbeitszeit) {
            $arbeitszeit->pausen()->delete();
            // do the rest of the cleanup...
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'beginn_bestaetigt' => 'boolean',
        'ende_bestaetigt' => 'boolean',
    ];


    /**
     * Relationships to the User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to Pausen
     */
    public function pausen()
    {
        return $this->hasMany(Pause::class);
    }

    public function calculateArbeitszeit()
    {

        if ($this->ende == null || ($this->frei_urlaub_krank != null && $this->beginn == null)) {
            $this->pausenzeit = 0 ;
            return 0;
        }

        $start = strtotime($this->beginn);
        $end = strtotime($this->ende);
        $stunden = round(($end - $start) / 3600, 2);
        $pausenZeit = 0;
        foreach ($this->pausen as $pause) {
            $pausenZeit += $pause->calculatePausenzeit();
        }
        if ($stunden > 6) {
            if ($pausenZeit < 0.5) {
                $pausenZeit = 0.5;
            }
        }
        $stunden -= $pausenZeit;
        if ($stunden > 9 && $pausenZeit < 0.75) {
            $stunden -= 0.15;
            $pausenZeit = 0.75;
        }
        $this->pausenzeit = $pausenZeit * 60;
        return $stunden;
    }

    // function to get difference between two times minus pause time
    public function getDifference($beginn, $ende)
    {
        $start = strtotime($beginn);
        $end = strtotime($ende);
        $difference = round(($end - $start) / 3600, 2);
        $pausenZeit = 0;
        foreach ($this->pausen as $pause) {
            $pausenZeit += $pause->calculatePausenzeit();
        }
        $difference -= $pausenZeit;
        return $difference;
    }

    public function getReadableArbeitszeitAttribute()
    {
        return Carbon::parse($this->ende)->subMinutes($this->pausenzeit)
            ->diff(Carbon::parse($this->beginn),true)
            ->format('%H:%I');
    }

    public function getArbeitszeitInMinutesAttribute()
    {
        return Carbon::parse($this->ende)->subMinutes($this->pausenzeit)
            ->diffInMinutes(Carbon::parse($this->beginn),true);
    }


    // Show frei_urlaub_krank or arbeitszeit
    public function getReadableTimeAttribute($value)
    {
        if ($this->frei_urlaub_krank) {
            return $this->optionLabels[$this->frei_urlaub_krank];
        } elseif ($this->arbeitszeit) {
            return $this->readable_arbeitszeit.' h';
        } else {
            return null;
        }
    }

    public function getReadableFutureTimeAttribute($value)
    {
        if ($this->frei_urlaub_krank) {
            return $this->optionLabels[$this->frei_urlaub_krank];
        } elseif ($this->beginn && $this->ende) {
            // remove seconds from beginn and ende
            $beginn = substr($this->beginn, 0, 5);
            $ende = substr($this->ende, 0, 5);
            return $beginn.' - '.$ende;
        } else {
            return null;
        }
    }

    public function getPausenzeitInMinutenAttribute() :int
    {
        $pausenzeit = 0;
        foreach ($this->pausen()->get() as $pausen) {
            $pausenzeit += intval($pausen->calculatePausenzeit() *60);
        }
        return $pausenzeit;
    }

    public function getDayAttribute() : Carbon
    {
        return Carbon::parse($this->tag);
    }

}
