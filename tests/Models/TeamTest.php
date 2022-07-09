<?php

namespace Tobb10001\H4aIntegration\Models;

use PHPUnit\Framework\TestCase;

/**
 * @covers Tobb10001\H4aIntegration\Models\Team
 */
class TeamTest extends TestCase
{
    public function testConstructorMinimal()
    {
        $team = new Team([
            "internalName" => "TeamOne"
        ]);
        $this->assertEquals("TeamOne", $team->internalName);
        $this->assertEquals([], $team->identificators);
    }

    public function testConstructorIdentificators()
    {
        $team = new Team([
            "internalName" => "TeamOne",
            "identificators" => ""
        ]);
        $this->assertEquals([], $team->identificators);

        $team = new Team([
            "internalName" => "TeamOne",
            "identificators" => null
        ]);
        $this->assertEquals([], $team->identificators);

        $team = new Team([
            "internalName" => "TeamOne",
            "identificators" => "Team"
        ]);
        $this->assertEquals(["Team"], $team->identificators);

        $team = new Team([
            "internalName" => "TeamOne",
            "identificators" => "Team,One"
        ]);
        $this->assertEquals(["Team","One"], $team->identificators);

        $team = new Team([
            "internalName" => "TeamOne",
            "identificators" => ["Team","One"]
        ]);
        $this->assertEquals(["Team","One"], $team->identificators);
    }

    public function testConstructorComplete()
    {
        $team = new Team([
            "id" => 1,
            "internalName" => "TeamOne",
            "identificators" => "Team,One",
            "leagueUrl" => "leagueUrl",
            "cupUrl" => "cupUrl"
        ]);
        $this->assertEquals([
            "id" => 1,
            "internalName" => "TeamOne",
            "identificators" => ["Team", "One"],
            "leagueUrl" => "leagueUrl",
            "cupUrl" => "cupUrl"
        ], [
            "id" => $team->id,
            "internalName" => $team->internalName,
            "identificators" => $team->identificators,
            "leagueUrl" => $team->leagueUrl,
            "cupUrl" => $team->cupUrl
        ]);
    }

    public function testConstructorIdVariants()
    {
        $team = new Team([
            "internalName" => "TeamOne",
        ]);
        $this->assertNull($team->id);

        $team = new Team([
            "internalName" => "TeamOne",
            "id" => null,
        ]);
        $this->assertNull($team->id);

        $team = new Team([
            "internalName" => "TeamOne",
            "id" => "",
        ]);
        $this->assertNull($team->id);

        $team = new Team([
            "internalName" => "TeamOne",
            "id" => 5,
        ]);
        $this->assertEquals(5, $team->id);

        $team = new Team([
            "internalName" => "TeamOne",
            "id" => "5",
        ]);
        $this->assertEquals(5, $team->id);
    }
}
