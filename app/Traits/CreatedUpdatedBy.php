<?php

namespace App\Traits;

use Tymon\JWTAuth\Facades\JWTAuth;

trait CreatedUpdatedBy
{
    public static function bootCreatedUpdatedBy()
    {
        // updating created_by and updated_by when model is created
        static::creating(function ($model) {
            $payload = JWTAuth::parseToken()->getPayload();
            // dd($payload);
            if (!$model->isDirty('created_by')) {
                $model->created_by = $payload['id'];
            }
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = $payload['id'];
            }
        });

        // updating updated_by when model is updated
        static::updating(function ($model) {
            $payload = JWTAuth::parseToken()->getPayload();
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = $payload['id'];
            }
        });
    }
}
