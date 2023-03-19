<?php


namespace App\Collectors;

use App\Models\Club;
use App\Models\Teams;
use App\Models\User;
use Arquivei\LaravelPrometheusExporter\CollectorInterface;
use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use Prometheus\Counter;
use Prometheus\Gauge;

class Clubs implements CollectorInterface
{

    private Gauge $totalClubs;

    public function getName() : string
    {
        return 'clubs';
    }

    public function registerMetrics(PrometheusExporter $exporter) : void
    {
        $this->totalClubs = $exporter->registerGauge('clubs_total', 'The total number of clubs initialized.');
    }

    public function collect() : void
    {
        $count = Club::query()->where('initialized', '=', 1)->count();
        $this->totalClubs->set($count);
    }
}
