<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Doctor;
use App\Models\MedicalRep;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
    }
}
