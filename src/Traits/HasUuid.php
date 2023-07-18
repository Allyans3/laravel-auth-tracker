<?php

namespace ALajusticia\AuthTracker\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        if (config('auth_tracker.id_type') === 'uuid')
            static::creating(function ($model) {
                if (empty($model->{$model->getKeyName()})) {
                    $model->{$model->getKeyName()} = Str::uuid4()->toString();
                }
            });
    }
    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing(): bool
    {
        if (config('auth_tracker.id_type') === 'uuid')
            return false;
        else
            return true;
    }
    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType(): string
    {
        if (config('auth_tracker.id_type') === 'uuid')
            return 'string';
        else
            return 'int';
    }
}