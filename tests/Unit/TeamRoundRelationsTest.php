<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Cancellation;
use App\Models\Squad;
use App\Models\TeamActivityLog;
use App\Models\TeamReceivers;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class TeamRoundRelationsTest extends TestCase
{
    /**
     * @test
     */
    public function squad_has_canonical_team_round_relation(): void
    {
        $squad = new Squad();

        $teamRoundRelation = $squad->teamRound();

        $this->assertInstanceOf(BelongsTo::class, $teamRoundRelation);
        $this->assertSame(TeamRound::class, $teamRoundRelation->getRelated()::class);
        $this->assertSame('team_round_id', $teamRoundRelation->getForeignKeyName());
    }

    /**
     * @test
     */
    public function cancellation_has_canonical_team_round_relation(): void
    {
        $cancellation = new Cancellation();

        $teamRoundRelation = $cancellation->teamRound();

        $this->assertInstanceOf(BelongsTo::class, $teamRoundRelation);
        $this->assertSame(TeamRound::class, $teamRoundRelation->getRelated()::class);
        $this->assertSame('team_round_id', $teamRoundRelation->getForeignKeyName());
    }

    /**
     * @test
     */
    public function team_receivers_has_canonical_team_round_relation(): void
    {
        $teamReceivers = new TeamReceivers();

        $teamRoundRelation = $teamReceivers->teamRound();

        $this->assertInstanceOf(BelongsTo::class, $teamRoundRelation);
        $this->assertSame(TeamRound::class, $teamRoundRelation->getRelated()::class);
        $this->assertSame('team_round_id', $teamRoundRelation->getForeignKeyName());
    }

    /**
     * @test
     */
    public function team_activity_log_has_canonical_team_round_relation(): void
    {
        $teamActivityLog = new TeamActivityLog();

        $teamRoundRelation = $teamActivityLog->teamRound();

        $this->assertInstanceOf(BelongsTo::class, $teamRoundRelation);
        $this->assertSame(TeamRound::class, $teamRoundRelation->getRelated()::class);
        $this->assertSame('team_round_id', $teamRoundRelation->getForeignKeyName());
    }

    /**
     * @test
     */
    public function user_has_canonical_team_rounds_relation(): void
    {
        $user = new User();

        $teamRoundsRelation = $user->teamRounds();

        $this->assertInstanceOf(HasMany::class, $teamRoundsRelation);
        $this->assertSame(TeamRound::class, $teamRoundsRelation->getRelated()::class);
        $this->assertSame('user_id', $teamRoundsRelation->getForeignKeyName());
    }

    /**
     * @test
     */
    public function legacy_alias_relation_methods_are_removed(): void
    {
        $this->assertFalse(method_exists(Squad::class, 'team'));
        $this->assertFalse(method_exists(Cancellation::class, 'team'));
        $this->assertFalse(method_exists(TeamReceivers::class, 'team'));
        $this->assertFalse(method_exists(TeamReceivers::class, 'teams'));
        $this->assertFalse(method_exists(TeamActivityLog::class, 'team'));
        $this->assertFalse(method_exists(User::class, 'teams'));
    }
}
