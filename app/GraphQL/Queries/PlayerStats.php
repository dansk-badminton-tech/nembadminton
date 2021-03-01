<?php
declare(strict_types = 1);


namespace App\GraphQL\Queries;

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
        $badmintonId = $args['badmintonId'] ?? '';
        $select = ['points', 'position', 'version'];

        return [
            'level'       => $this->getLevelPoints($select, $badmintonId),
            'mixWomen'    => $this->getCategoryPointAndPosition($badmintonId, 'MxD', $select),
            'mixMen'      => $this->getCategoryPointAndPosition($badmintonId, 'MxH', $select),
            'singleWomen' => $this->getCategoryPointAndPosition($badmintonId, 'DS', $select),
            'singleMen'   => $this->getCategoryPointAndPosition($badmintonId, 'HS', $select),
            'doubleWomen' => $this->getCategoryPointAndPosition($badmintonId, 'DD', $select),
            'doubleMen'   => $this->getCategoryPointAndPosition($badmintonId, 'HD', $select),
        ];
    }

    /**
     * @param array $select
     * @param       $badmintonId
     *
     * @return array
     */
    protected function getLevelPoints(array $select, string $badmintonId) : array
    {
        return Point::query()->select($select)->whereNull('category')->whereHas('member', function (Builder $query) use ($badmintonId) {
            $query->where('refId', $badmintonId);
        })->orderBy('version')->get()->toArray();
    }

    /**
     * @param string $badmintonId
     * @param string $category
     * @param array  $select
     *
     * @return array
     */
    protected function getCategoryPointAndPosition(string $badmintonId, string $category, array $select) : array
    {
        return Point::query()->select($select)->where('category', $category)->whereHas('member', function (Builder $query) use ($badmintonId) {
            $query->where('refId', $badmintonId);
        })->orderBy('version')->get()->toArray();
    }
}
