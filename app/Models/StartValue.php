<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StartValue
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property string $year
 * @property int $resturlaub
 * @property int $urlaub
 * @property float $ueberstunden
 * @property float $ueberstunden_aktuell
 * @property int $resturlaub_aktuell
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue query()
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue whereResturlaub($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue whereResturlaubAktuell($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue whereUeberstunden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue whereUeberstundenAktuell($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue whereUrlaub($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartValue whereYear($value)
 * @mixin \Eloquent
 */
class StartValue extends Model
{
    use HasFactory;
    public $fillable = ["year","user_id"];
}
