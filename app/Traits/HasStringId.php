<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasStringId
{
    protected static function bootHasStringId()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = bin2hex(random_bytes(12));
            }
        });
    }
    
    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}
