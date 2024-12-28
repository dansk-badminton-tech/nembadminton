<?php


namespace App\GraphQL\Queries;

use App\Models\CancellationCollector;
use App\Models\CancellationDate;
use Carbon\Carbon;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CancellationEvents
{

    /**
     * @param                      $rootValue
     * @param array<string, mixed> $args
     * @param GraphQLContext       $context
     * @param ResolveInfo          $resolveInfo
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {

        /** @var CancellationCollector $cancellationCollector */
        $cancellationCollector = CancellationCollector::query()->findOrFail($args['id']);

        $cancellationDates = CancellationDate::query()
                                             ->selectRaw('date, count(*) as count')
                                             ->whereHas('cancellation', function (Builder $query) use ($args) {
                                                 $query->where('cancellation_collector_id', $args['id']);
                                             })
                                             ->groupBy('date')
                                             ->pluck('count', 'date');

        return $cancellationDates->map(function ($count, $date) {
            return [
                'title'       => 'Total afbud: ' . $count,
                'start'       => Carbon::parse($date),
                'end'         => Carbon::parse($date),
                'content'     => 'Klik for info',
                'matchId'     => '',
                'contentFull' => 'Total antal afbud på ' . $date . ': ' . $count,
            ];
        });
    }

}
