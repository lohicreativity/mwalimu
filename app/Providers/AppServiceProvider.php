<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Domain\Academic\Models\RetakeHistory;
use App\Domain\Academic\Models\CarryHistory;
use App\Domain\Academic\Models\Appeal;
use App\Domain\Registration\Models\Student;
use App\Domain\Application\Models\Applicant;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\CourseWorkResult;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Relation::morphMap([
            'retake_history'=>RetakeHistory::class,
            'carry_history'=>CarryHistory::class,
            'student'=>Student::class,
            'applicant'=>Applicant::class,
            'user'=>User::class,
            'appeal'=>Appeal::class,
            'academic_year'=>StudyAcademicYear::class,
            'application_window'=>ApplicationWindow::class,
            'examination_result'=>ExaminationResult::class,
            'course_work_result'=>CourseWorkResult::class
        ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return 'Database\Factories\\'.class_basename($modelName).'Factory';
         });
    }
}
