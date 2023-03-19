<?php


namespace App\Collectors;

use App\Models\Club;
use App\Models\Teams;
use App\Models\User;
use Arquivei\LaravelPrometheusExporter\CollectorInterface;
use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use Prometheus\Counter;
use Prometheus\Gauge;

class Users implements CollectorInterface
{

    private Gauge $totalUsers;

    public function getName() : string
    {
        return 'users';
    }

    public function registerMetrics(PrometheusExporter $exporter) : void
    {
        $this->totalUsers = $exporter->registerGauge('users_total', 'The total number of users.');
    }

    public function collect() : void
    {
        $count = User::query()->count();
        $this->totalUsers->set($count);
    }
}
