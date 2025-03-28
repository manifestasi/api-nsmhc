<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Symfony\Component\Uid\UuidV7;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->id = UuidV7::v7()->toRfc4122();
            }
        });
    }

    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'users_id');
    }

    public function userHusband(): HasOne
    {
        return $this->hasOne(UserHusband::class, 'users_id');
    }

    public function userChild(): HasOne
    {
        return $this->hasOne(UserChild::class, 'users_id');
    }

    public function reactions(): BelongsToMany
    {
        return $this->belongsToMany(
            Reaction::class,
            'user_reactions',
            'users_id',
            'reactions_id'
        );
    }

    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(
            Content::class,
            'user_contents',
            'users_id',
            'contents_id'
        );
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? false, function ($query, $search) {
            return $query->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
        });
    }
}
