<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViewMembersWithPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement($this->dropView());
    }

    private function createView(): string
    {
        return <<<SQL
CREATE OR REPLACE VIEW members_with_latest_points AS SELECT members.*, ( SELECT points.points FROM points WHERE members.id = points.member_id AND points.category IS NULL AND points.version =( SELECT MAX(VERSION) FROM points ) ) AS latest_level_points, ( SELECT points.position FROM points WHERE members.id = points.member_id AND points.category IS NULL AND points.version =( SELECT MAX(VERSION) FROM points ) ) AS latest_level_position, ( SELECT MAX(VERSION) FROM points ) AS latest_level_version FROM members;
SQL;
    }

    private function dropView(): string
    {
        return <<<SQL
DROP VIEW IF EXISTS `members_with_latest_points`;
SQL;
    }
}
