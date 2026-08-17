<?php



namespace App\Providers;



use Illuminate\Cache\RateLimiting\Limit;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\RateLimiter;

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

        RateLimiter::for('nacs-sensitive-write', function (Request $request): Limit {

            $routeName = (string) ($request->route()?->getName() ?? 'unknown');

            $actor = (string) ($request->user()?->getAuthIdentifier() ?? $request->ip());



            [$minutes, $attempts] = match ($routeName) {

                'admin.students.grades.store' => [1, 30],

                'admin.students.grades.destroy' => [1, 20],

                'admin.students.attendance.store' => [1, 30],

                'admin.students.finance.store' => [10, 10],

                'admin.students.assignments.store',

                'admin.students.assignments.approve',

                'admin.students.assignments.reject',

                'admin.students.assignments.destroy',

                'admin.students.guardians.store' => [10, 10],

                'admin.students.documents.store' => [10, 5],

                'admissions.documents.destroy' => [60, 5],

                'admin.media.store',

                'admin.media.destroy' => [10, 10],

                'admin.branding.store',

                'admin.branding.destroy' => [10, 5],

                'admin.staff.store' => [10, 5],

                'admin.staff.update' => [10, 10],

                'admin.staff.reset-two-factor' => [10, 3],

                default => [10, 5],

            };



            return Limit::perMinutes($minutes, $attempts)

                ->by('nacs-sensitive-write|'.$routeName.'|'.$actor);

        });

    }

}