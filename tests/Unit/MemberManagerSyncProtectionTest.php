<?php

namespace Tests\Unit;

use App\Models\Member;
use FlyCompany\Members\MemberManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberManagerSyncProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private MemberManager $memberManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->memberManager = new MemberManager();
    }

    /** @test */
    public function it_updates_inactive_when_override_inactive_is_auto(): void
    {
        $member = Member::create([
            'refId'             => '12345',
            'name'              => 'Test Player',
            'gender'            => 'M',
            'inactive'          => false,
            'override_inactive' => 'AUTO',
        ]);

        // Sync arrives stating player is inactive (active = false)
        $this->memberManager->addOrUpdateMember('12345', 'Test Player', 'M', active: false);

        $member->refresh();
        $this->assertTrue($member->inactive);
        $this->assertEquals('AUTO', $member->override_inactive);
    }

    /** @test */
    public function it_protects_force_active_members_from_being_marked_inactive(): void
    {
        $member = Member::create([
            'refId'             => '12345',
            'name'              => 'Test Player',
            'gender'            => 'M',
            'inactive'          => false,
            'override_inactive' => 'FORCE_ACTIVE',
        ]);

        // Sync arrives stating player is inactive (active = false)
        $this->memberManager->addOrUpdateMember('12345', 'Test Player', 'M', active: false);

        $member->refresh();
        $this->assertFalse($member->inactive);
        $this->assertEquals('FORCE_ACTIVE', $member->override_inactive);
    }

    /** @test */
    public function it_protects_force_inactive_members_from_being_marked_active(): void
    {
        $member = Member::create([
            'refId'             => '12345',
            'name'              => 'Test Player',
            'gender'            => 'M',
            'inactive'          => true,
            'override_inactive' => 'FORCE_INACTIVE',
        ]);

        // Sync arrives stating player is active (active = true)
        $this->memberManager->addOrUpdateMember('12345', 'Test Player', 'M', active: true);

        $member->refresh();
        $this->assertTrue($member->inactive);
        $this->assertEquals('FORCE_INACTIVE', $member->override_inactive);
    }

    /** @test */
    public function it_updates_inactive_after_member_override_is_reset_to_auto(): void
    {
        $member = Member::create([
            'refId'             => '12345',
            'name'              => 'Test Player',
            'gender'            => 'M',
            'inactive'          => true,
            'override_inactive' => 'FORCE_INACTIVE',
        ]);

        // Player override is reset to AUTO
        $member->override_inactive = 'AUTO';
        $member->save();

        // Next sync arrives stating player is active (active = true)
        $this->memberManager->addOrUpdateMember('12345', 'Test Player', 'M', active: true);

        $member->refresh();
        $this->assertFalse($member->inactive);
        $this->assertEquals('AUTO', $member->override_inactive);
    }
}
