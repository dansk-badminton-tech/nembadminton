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

class TeamRoundRelationAliasesTest extends TestCase
{
    /**
     * @test
     */
    public function squad_has_canonical_team_round_relation_and_backwards_compatible_alias(): void
    {
        $squad = new Squad();

        $teamRoundRelation = $squad->teamRound();
        $teamAliasRelation = $squad->team();

        $this->assertInstanceOf(BelongsTo::class, $teamRoundRelation);
        $this->assertSame(TeamRound::class, $teamRoundRelation->getRelated()::class);
        $this->assertSame($teamRoundRelation->getForeignKeyName(), $teamAliasRelation->getForeignKeyName());
        $this->assertSame($teamRoundRelation->getOwnerKeyName(), $teamAliasRelation->getOwnerKeyName());
    }

    /**
     * @test
     */
    public function cancellation_has_canonical_team_round_relation_and_backwards_compatible_alias(): void
    {
        $cancellation = new Cancellation();

        $teamRoundRelation = $cancellation->teamRound();
        $teamAliasRelation = $cancellation->team();

        $this->assertInstanceOf(BelongsTo::class, $teamRoundRelation);
        $this->assertSame(TeamRound::class, $teamRoundRelation->getRelated()::class);
        $this->assertSame($teamRoundRelation->getForeignKeyName(), $teamAliasRelation->getForeignKeyName());
        $this->assertSame($teamRoundRelation->getOwnerKeyName(), $teamAliasRelation->getOwnerKeyName());
    }

    /**
     * @test
     */
    public function team_receivers_has_canonical_relation_and_both_legacy_aliases(): void
    {
        $teamReceivers = new TeamReceivers();

        $teamRoundRelation = $teamReceivers->teamRound();
        $teamAliasRelation = $teamReceivers->team();
        $teamsAliasRelation = $teamReceivers->teams();

        $this->assertInstanceOf(BelongsTo::class, $teamRoundRelation);
        $this->assertSame(TeamRound::class, $teamRoundRelation->getRelated()::class);
        $this->assertSame($teamRoundRelation->getForeignKeyName(), $teamAliasRelation->getForeignKeyName());
        $this->assertSame($teamRoundRelation->getForeignKeyName(), $teamsAliasRelation->getForeignKeyName());
    }

    /**
     * @test
     */
    public function team_activity_log_has_canonical_team_round_relation_and_backwards_compatible_alias(): void
    {
        $teamActivityLog = new TeamActivityLog();

        $teamRoundRelation = $teamActivityLog->teamRound();
        $teamAliasRelation = $teamActivityLog->team();

        $this->assertInstanceOf(BelongsTo::class, $teamRoundRelation);
        $this->assertSame(TeamRound::class, $teamRoundRelation->getRelated()::class);
        $this->assertSame($teamRoundRelation->getForeignKeyName(), $teamAliasRelation->getForeignKeyName());
    }

    /**
     * @test
     */
    public function user_has_canonical_team_rounds_relation_and_backwards_compatible_alias(): void
    {
        $user = new User();

        $teamRoundsRelation = $user->teamRounds();
        $teamsAliasRelation = $user->teams();

        $this->assertInstanceOf(HasMany::class, $teamRoundsRelation);
        $this->assertSame(TeamRound::class, $teamRoundsRelation->getRelated()::class);
        $this->assertSame($teamRoundsRelation->getForeignKeyName(), $teamsAliasRelation->getForeignKeyName());
        $this->assertSame($teamRoundsRelation->getLocalKeyName(), $teamsAliasRelation->getLocalKeyName());
    }
}
