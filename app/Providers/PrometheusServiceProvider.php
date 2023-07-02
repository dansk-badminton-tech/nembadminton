<?php
namespace App\Providers;

use App\Models\Club;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Spatie\Prometheus\Collectors\Horizon\CurrentMasterSupervisorCollector;
use Spatie\Prometheus\Collectors\Horizon\CurrentProcessesPerQueueCollector;
use Spatie\Prometheus\Collectors\Horizon\CurrentWorkloadCollector;
use Spatie\Prometheus\Collectors\Horizon\FailedJobsPerHourCollector;
use Spatie\Prometheus\Collectors\Horizon\HorizonStatusCollector;
use Spatie\Prometheus\Collectors\Horizon\JobsPerMinuteCollector;
use Spatie\Prometheus\Collectors\Horizon\RecentJobsCollector;
use Spatie\Prometheus\Facades\Prometheus;

class PrometheusServiceProvider extends ServiceProvider
{
    public function register()
    {
        /*
         * Here you can register all the exporters that you
         * want to export to prometheus
         */
        Prometheus::addGauge('clubs_total')->value(static function () {
            return Club::query()->where('initialized', '=', 1)->count();
        })->helpText('The total number of clubs initialized.');

        Prometheus::addGauge('team_fights_total')->value(static function () {
            return Teams::query()->count();
        })->helpText('The total number of team fights.');

        Prometheus::addGauge('team_fights')
            ->labels(['club_id', 'name'])
            ->value(static function () {
                $teamsByClubs = Teams::query()->groupBy('club_id')->selectRaw('club_id, count(*) as total')->get();
                return $teamsByClubs->map(function($teamsByClub){
                    /** @var Club $club */
                    $club = Club::query()->where('id', '=',$teamsByClub->club_id)->firstOrFail();
                    return [$teamsByClub->total, ['club_id' => $teamsByClub->club_id, 'name' => $club->name1]];
                })->toArray();
        })->helpText('The total number of team fights by club.');

        Prometheus::addGauge('users_total')->value(static function () {
            return User::query()->count();
        })->helpText('The total number of users.');

        /*
         * Uncomment this line if you want to export
         * all Horizon metrics to prometheus
         */
        //$this->registerHorizonCollectors();
    }

    public function registerHorizonCollectors(): self
    {
        Prometheus::registerCollectorClasses([
            CurrentMasterSupervisorCollector::class,
            CurrentProcessesPerQueueCollector::class,
            CurrentWorkloadCollector::class,
            FailedJobsPerHourCollector::class,
            HorizonStatusCollector::class,
            JobsPerMinuteCollector::class,
            RecentJobsCollector::class,
        ]);

        return $this;
    }
}
