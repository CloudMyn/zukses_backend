<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('email_or_phone', function ($attribute, $value, $parameters, $validator) {
            return filter_var($value, FILTER_VALIDATE_EMAIL) || preg_match('/^[0-9]{10,15}$/', $value);
        });

        Validator::replacer('email_or_phone', function ($message, $attribute, $rule, $parameters) {
            return 'The ' . $attribute . ' must be a valid email address or phone number.';
        });
    }
}
