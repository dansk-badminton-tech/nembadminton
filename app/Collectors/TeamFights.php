<?php


namespace App\Collectors;

use App\Models\Club;
use App\Models\Teams;
use Arquivei\LaravelPrometheusExporter\CollectorInterface;
use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use Prometheus\Counter;
use Prometheus\Gauge;

class TeamFights implements CollectorInterface
{

    private Gauge $totalTeamFights;

    private Gauge $totalTeamFightsByClub;

    public function getName() : string
    {
        return 'team_fights';
    }

    public function registerMetrics(PrometheusExporter $exporter) : void
    {
        $this->totalTeamFights = $exporter->registerGauge('team_fights_total', 'The total number of team fights.');
        $this->totalTeamFightsByClub = $exporter->registerGauge('team_fights', 'The total number of team fights by club.', ['club_id', 'name']);
    }

    public function collect() : void
    {
        $count = Teams::query()->count();
        $this->totalTeamFights->set($count);

        $teamsByClubs = Teams::query()->groupBy('club_id')->selectRaw('club_id, count(*) as total')->get();
        $teamsByClubs->map(function($teamsByClub){
            /** @var Club $club */
            $club = Club::query()->where('id', '=',$teamsByClub->club_id)->firstOrFail();
            $this->totalTeamFightsByClub->set($teamsByClub->total, ['club_id' => $teamsByClub->club_id, 'name' => $club->name1]);
        });
    }
}
