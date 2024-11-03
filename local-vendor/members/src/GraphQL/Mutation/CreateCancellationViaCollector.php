<?php


namespace FlyCompany\Members\GraphQL\Mutation;

use App\Models\CancellationCollector;
use App\Models\CancellationPublic;
use App\Models\Member;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use FlyCompany\Scraper\Helper;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CreateCancellationViaCollector
{

    /**
     * Return a value for the field.
     *
     * @param  @param  null  $root Always null, since this field has no parent.
     * @param array<string, mixed>                                $args        The field arguments passed by the client.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context     Shared between all fields.
     * @param \GraphQL\Type\Definition\ResolveInfo                $resolveInfo Metadata for advanced query resolution.
     *
     * @return mixed
     * @throws \JsonException
     * @throws \Throwable
     *
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        //dump($args);
        /** @var CancellationCollector $collector */
        $collector = CancellationCollector::query()->where('sharing_id', $args['sharingId'])->firstOrFail();

        /** @var Member $member */
        $member = Member::query()->where('refId', $args['input']['refId'])->firstOrFail();
        $member->email = $args['input']['email'];
        $member->save();

        /** @var CancellationPublic $cancellation */
        $cancellation = CancellationPublic::forceCreate([
            'member_id' => $member->id,
            'cancellation_collector_id' => $collector->id,
            'message' => $args['input']['message'],
        ]);

        $cancellation->dates()->createMany(array_map(function ($date){
            return ['date' => $date];
        }, $args['input']['dates']));

        //dd($args);

    }

}
