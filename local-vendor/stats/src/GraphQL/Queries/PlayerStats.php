<?php
declare(strict_types = 1);


namespace FlyCompany\Stats\GraphQL\Queries;

use App\Models\Clubhouse;
use App\Models\Member;
use FlyCompany\Members\Enums\Category;
use FlyCompany\Stats\Stats;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class PlayerStats
{

    public function __construct(private Stats $stats)
    {
    }

    /**
     * Return a value for the field.
     *
     * @param  @param  null  $root Always null, since this field has no parent.
     * @param array<string, mixed>                                $args        The field arguments passed by the client.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context     Shared between all fields.
     * @param \GraphQL\Type\Definition\ResolveInfo                $resolveInfo Metadata for advanced query resolution.
     *
     * @return mixed
     */
    public function stats($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $memberId = $args['id'];

        return $this->getData($memberId);
    }

    public function bulkStats($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $memberIds = $args['ids'];

        $data = [];

        foreach ($memberIds as $memberId) {
            $data[] = $this->getData($memberId);
        }

        return $data;
    }

    public function highestPointGain($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $clubhouse = Clubhouse::query()->findOrFail($args['clubhouseId']);

        return $this->stats->getLowToHighestPoints($clubhouse->clubs()->pluck('id')->toArray(), Category::tryFrom($args['category']), $args['limit'], $args['orderBy'], $args['vintages']);
    }

    /**
     * @param mixed $memberId
     *
     * @return Member[]
     */
    private function getData(mixed $memberId) : array
    {
        /** @var Member $member */
        $member = Member::query()->where('id', $memberId)->firstOrFail();
        $data = [
            'member' => $member,
        ];
        if ($member->isWomen()) {
            $data['mix'] = $this->stats->getCategoryPoint($memberId, Category::from('MxD'));
            $data['single'] = $this->stats->getCategoryPoint($memberId, Category::from('DS'));
            $data['double'] = $this->stats->getCategoryPoint($memberId, Category::from('DD'));
        } else {
            $data['mix'] = $this->stats->getCategoryPoint($memberId, Category::from('MxH'));
            $data['single'] = $this->stats->getCategoryPoint($memberId, Category::from('HS'));
            $data['double'] = $this->stats->getCategoryPoint($memberId, Category::from('HD'));
        }

        return $data;
    }
}
