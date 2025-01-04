<?php
declare(strict_types = 1);


namespace FlyCompany\Stats\GraphQL\Queries;

use App\Models\Member;
use App\Models\Point;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class PlayerStats
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
     */
    public function stats($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $memberId = $args['id'];

        /** @var Member $member */
        $member = Member::query()->where('id', $memberId)->firstOrFail();

        $data = [
            'member'      => $member
        ];

        if($member->isWomen()){
            $data['mix'] = $this->getCategoryPointAndPosition($memberId, 'MxD');
            $data['single'] = $this->getCategoryPointAndPosition($memberId, 'DS');
            $data['double'] = $this->getCategoryPointAndPosition($memberId, 'DD');
        }else{
            $data['mix'] = $this->getCategoryPointAndPosition($memberId, 'MxH');
            $data['single'] = $this->getCategoryPointAndPosition($memberId, 'HS');
            $data['double'] = $this->getCategoryPointAndPosition($memberId, 'HD');
        }

        return $data;
    }

    /**
     * @param string $memberId
     * @param string $category
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getCategoryPointAndPosition(string $memberId, string $category) : \Illuminate\Database\Eloquent\Collection
    {
        return Point::query()
                    ->where('category', $category)
                    ->whereHas('member', function (Builder $query) use ($memberId) {
                        $query->where('id', $memberId);
                    })->orderBy('version')->get();
    }
}
