<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\UuidV7;

class UserHusband extends Model
{
    protected $guarded = ['id'];

    protected $keyType = 'string';

    public $timestamps = false;

    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->id = UuidV7::v7()->toRfc4122();
            }
        });
    }
}
