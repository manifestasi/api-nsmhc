<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Content extends Model
{
    protected $guarded = ['id'];


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_contents',
            'contents_id',
            'users_id'
        );
    }
}
