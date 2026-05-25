<?php

namespace App\Providers;

use App\Auth\PasswordBrokerManager;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Doctor;
use App\Models\MedicalRep;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('auth.password', function ($app) {
            return new PasswordBrokerManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'doctor' => \App\Models\Doctor::class,
            'company' => \App\Models\Company::class,
            'medical_rep' => \App\Models\MedicalRep::class,
            'admin' => \App\Models\Admin::class,
        ]);

        $this->app->booted(function () {
            $this->app->make('migrator')->path(database_path('migrations'.DIRECTORY_SEPARATOR.'E_REP'));
        });

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $role = match ($notifiable::class) {
                Admin::class => 'admin',
                Company::class => 'company',
                Doctor::class => 'doctor',
                MedicalRep::class => 'rep',
                default => 'user',
            };

            $baseUrl = rtrim((string) config('app.url'), '/');

            return $baseUrl.'/reset-password/'.$role.'?'.http_build_query([
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });
    }
}
