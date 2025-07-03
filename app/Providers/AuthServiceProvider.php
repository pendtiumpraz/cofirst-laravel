<?php

namespace App\Providers;

use App\Models\ClassReport;
use App\Models\Curriculum;
use App\Models\Syllabus;
use App\Models\Material;
use App\Policies\ClassReportPolicy;
use App\Policies\CurriculumPolicy;
use App\Policies\SyllabusPolicy;
use App\Policies\MaterialPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        ClassReport::class => ClassReportPolicy::class,
        Curriculum::class => CurriculumPolicy::class,
        Syllabus::class => SyllabusPolicy::class,
        Material::class => MaterialPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}