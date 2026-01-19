<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Eloquent;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\EloquentSortable\SortableTrait;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool $arbeitszeit_admin
 * @property bool $arbeitszeit_teamleader
 * @property string|null $telegram_id
 * @property string|null $chip_id
 * @property-read Collection|Arbeitszeit[] $arbeitszeiten
 * @property-read int|null $arbeitszeiten_count
 * @property-read Collection|User[] $arbeitszeitenTeam
 * @property-read int|null $arbeitszeiten_team_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|Pause[] $pausen
 * @property-read int|null $pausen_count
 * @property-read Collection|PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 *
 * @method static UserFactory factory(...$parameters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereArbeitszeitAdmin($value)
 * @method static Builder|User whereArbeitszeitTeamleader($value)
 * @method static Builder|User whereChipId($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereTelegramId($value)
 * @method static Builder|User whereUpdatedAt($value)
 *
 * @mixin Eloquent
 *
 * @property string|null $deleted_at
 * @property-read mixed $readable_role
 *
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereRole($role)
 *
 * @property bool $keine_arbeitszeit
 * @property bool $minijob
 * @property int|null $sort
 * @property Carbon|null $eingestellt_am
 *
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static Builder|User ordered(string $direction = 'asc')
 * @method static Builder|User whereEingestelltAm($value)
 * @method static Builder|User whereKeineArbeitszeit($value)
 * @method static Builder|User whereMinijob($value)
 * @method static Builder|User whereSort($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 *
 * @property int $show_overtime
 *
 * @method static Builder|User whereShowOvertime($value)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, SortableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'arbeitszeit_admin',
        'arbeitszeit_teamleader',
        'telegram_id',
        'chip_id',
        'eingestellt_am',
        'druck_sort',
        'keine_arbeitszeit',
        'minijob',
        'api_qr_data',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'eingestellt_am' => 'date',
        'arbeitszeit_admin' => 'boolean',
        'arbeitszeit_teamleader' => 'boolean',
        'keine_arbeitszeit' => 'boolean',
        'minijob' => 'boolean',
    ];

    /**
     * The attributes that should be sortable.
     *
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    protected static function boot()
    {
        parent::boot();

        // Order by name ASC
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('druck_sort');
        });

        Gate::define('login-as-user', fn (User $user) => $user->isArbeitszeitenAdmin()
            ? Response::allow()
            : Response::deny('You must be an administrator.'));
    }

    /**
     * Arbeitszeiten Team
     */
    public function arbeitszeitenTeam()
    {
        return $this->belongsToMany(User::class, 'arbeitszeiten_team', 'user_id',
            'arbeitszeit_user_id')->withTimestamps();
    }

    public function arbeitszeitenTeamleiter()
    {
        return $this->belongsToMany(User::class, 'arbeitszeiten_team', 'arbeitszeit_user_id',
            'user_id')->withTimestamps();
    }
    /**
     * Realtionship to the Arbeitszeiten
     */
    public function arbeitszeiten()
    {
        return $this->hasMany(Arbeitszeit::class);
    }

    /**
     * Relationship to Pausen through Arbeitszeiten
     */
    public function pausen()
    {
        return $this->hasManyThrough(Pause::class, Arbeitszeit::class);
    }

    /**
     * Readable Attribute if is Arbeitszeiten Admin or Teamleader or User
     */
    public function getReadableRoleAttribute()
    {
        if ($this->isArbeitszeitenAdmin()) {
            $readable = 'Administrator';
        } elseif ($this->isArbeitszeitenTeamleader()) {
            $readable = 'Team-Leiter';
        } elseif ($this->minijob) {
            $readable = 'Minijobber';
        } else {
            $readable = 'Mitarbeiter';
        }

        if (config('tenant.gender_language')) {
            $readable = $readable.':in';
        }

        return $readable;
    }

    /**
     * Boolean if is Arbeitszeiten Admin
     */
    public function isArbeitszeitenAdmin()
    {
        return $this->arbeitszeit_admin;
    }

    /**
     * Boolean if is Arbeitszeiten Teamleader
     */
    public function isArbeitszeitenTeamleader()
    {
        return $this->arbeitszeit_teamleader || $this->arbeitszeit_admin;
    }

    /**
     * Boolean if is keine Arbeitszeit
     */
    public function isKeineArbeitszeit()
    {
        return $this->keine_arbeitszeit;
    }

    /**
     * Boolean if is minijob
     */
    public function isMinijob()
    {
        return $this->minijob;
    }

    /**
     * Scope over user Roles.
     *
     * @return mixed
     */
    public function scopeWhereRole($query, $role)
    {
        switch ($role) {
            case 'admin':
                return $query->where('arbeitszeit_admin', true);
            case 'teamleader':
                return $query->where('arbeitszeit_teamleader', true);
            case 'mitarbeiter':
                return $query->where(['arbeitszeit_teamleader' => false, 'arbeitszeit_admin' => false]);
        }
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::needsRehash($password) ? Hash::make($password) : $password;
    }

    public function routeNotificationForTelegram()
    {
        return $this->telegram_id;
    }

    public function startValues($year)
    {
        return $this->hasOne(StartValue::class)->whereYear('year', $year)->first();
    }

    public function minijobGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(MinijobGroup::class, 'minijobgroup_users_pivot', 'user_id', 'group_id')->withTimestamps();
    }

    /**
     * Relationship to JobGroups (for shift planning)
     */
    public function jobGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(JobGroup::class, 'job_group_user', 'user_id', 'job_group_id')->withTimestamps();
    }

    /**
     * Relationship to Shifts
     */
    public function shifts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'shift_user', 'user_id', 'shift_id')
            ->withPivot('user_comment')
            ->withTimestamps();
    }

    /**
     * Relationship to unavailable Shifts (Absagen)
     */
    public function unavailableShifts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'shift_unavailability', 'user_id', 'shift_id')
            ->withPivot('reason')
            ->withTimestamps();
    }

    public function getMinijobGroupAttribute(): MinijobGroup | null
    {
        return optional($this->minijobGroups)->first();
    }

    public function minijobVorgabe(int $year, int $month): MinijobVorgabe
    {
        return $this->minijob_group->vorgaben()->where('year', $year)->where('month', $month)->first();
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param  string  $name
     * @param  array  $abilities
     * @return NewAccessToken
     */
    public function createToken(string $name, array $abilities = ['*']): NewAccessToken
    {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(30)),
            'abilities' => $abilities,
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }
}
