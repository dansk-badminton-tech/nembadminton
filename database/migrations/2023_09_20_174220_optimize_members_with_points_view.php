<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->upSQL());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement($this->downSQL());
    }

    private function upSQL() : string
    {
        return <<<SQL
CREATE OR REPLACE view members_with_latest_points
AS
  SELECT
    m.*,
    p1.points AS latest_level_points,
    p1.position AS latest_level_position,
    (SELECT MAX(version) FROM points) AS latest_level_version
FROM members m

-- Join for the latest_level_points and latest_level_position
LEFT JOIN points p1
    ON m.id = p1.member_id
    AND p1.category IS NULL
    AND p1.version = (SELECT MAX(version) FROM points);
SQL;
    }

    private function downSQL() : string
    {
        return <<<SQL
CREATE OR REPLACE view members_with_latest_points
AS
  SELECT members.*,
         (SELECT points.points
          FROM   points
          WHERE  members.id = points.member_id
                 AND points.category IS NULL
                 AND points.version = (SELECT Max(version)
                                       FROM   points)) AS latest_level_points,
         (SELECT points.position
          FROM   points
          WHERE  members.id = points.member_id
                 AND points.category IS NULL
                 AND points.version = (SELECT Max(version)
                                       FROM   points)) AS latest_level_position,
         (SELECT Max(version)
          FROM   points)                               AS latest_level_version
  FROM   members;
SQL;
    }
};
