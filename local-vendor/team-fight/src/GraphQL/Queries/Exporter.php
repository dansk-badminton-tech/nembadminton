<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Queries;

use App\Models\Teams;
use FlyCompany\TeamFight\Export\Exporter as ConcreteExporter;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Exporter
{
    private ConcreteExporter $exporter;

    public function __construct(ConcreteExporter $exporter)
    {
        $this->exporter = $exporter;
    }

    /**
     * Return a value for the field.
     *
     * @param  @param  null  $root Always null, since this field has no parent.
     * @param array<string, mixed> $args The field arguments passed by the client.
     * @param GraphQLContext $context Shared between all fields.
     * @param ResolveInfo $resolveInfo Metadata for advanced query resolution.
     *
     * @return mixed
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : string
    {
        /** @var Teams $team */
        $teamId = $args['teamId'];
        $team = Teams::query()->where('id', $teamId)->where('clubhouse_id', $context->user()->clubhouse_id)->firstOrFail();
        $csvData = $this->exporter->exportToCSV($team);

        $randomNumber = date('d-m-Y_H-i-s');
        $filePath = "team-fight/exports/$teamId-$randomNumber.csv";
        $success = Storage::disk('public')->put($filePath, (chr(0xEF).chr(0xBB).chr(0xBF)).$csvData);
        if($success === false){
            throw new \RuntimeException('Failed to save '.$filePath);
        }

        return Storage::disk('public')->url($filePath);
    }

}
