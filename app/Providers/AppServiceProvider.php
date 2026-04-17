<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Doctor;
use App\Models\MedicalRep;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Migrations\Migrator;
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
            'doctor' => Doctor::class,
            'medical_rep' => MedicalRep::class,
            'company' => Company::class,
            'rep' => MedicalRep::class,
        ]);

        $this->app->booted(function () {
            $this->app['migrator']->path(database_path('migrations'.DIRECTORY_SEPARATOR.'E_REP'));
        });
    }
}
