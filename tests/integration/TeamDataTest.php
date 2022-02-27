<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

use PHPUnit\Framework\TestCase;

class LeagueDataTest extends TestCase
{
    /**
     * @covers Tobb10001\H4aIntegration\Models\Game
     * @covers Tobb10001\H4aIntegration\Models\GameSchedule
     * @covers Tobb10001\H4aIntegration\Models\LeagueData
     * @covers Tobb10001\H4aIntegration\Models\LeagueMetadata
     * @covers Tobb10001\H4aIntegration\Models\TabScore
     * @covers Tobb10001\H4aIntegration\Models\Table
     */
    public function testConstructLeagueDataFromJson(): void
    {
        $json = json_decode(
            file_get_contents(__DIR__ . "/../assets/league_response.json"),
            true
        );

        $leagueData = LeagueData::fromJson($json);

        $this->assertEquals(12, count($leagueData->table));
        $this->assertEquals(22, count($leagueData->games));

        $fs = $leagueData->table[0];
        $this->assertEquals([
            "tabScore" => 1,
            "tabTeamID" => "745797",
            "tabTeamname" => "TSV Speyer",
            "liveTeam" => true,
            "numPlayedGames" => 14,
            "numWonGames" => 14,
            "numEqualGames" => 0,
            "numLostGames" => 0,
            "numGoalsShot" => 387,
            "numGoalsGot" => 243,
            "pointsPlus" => 28,
            "pointsMinus" => 0,
            "pointsPerGame10" => "",
            "numGoalsDiffperGame" => "",
            "numGoalsShotperGame" => "",
            "posCriterion" => ""
        ], [
            "tabScore" => $fs->tabScore,
            "tabTeamID" => $fs->tabTeamID,
            "tabTeamname" => $fs->tabTeamname,
            "liveTeam" => $fs->liveTeam,
            "numPlayedGames" => $fs->numPlayedGames,
            "numWonGames" => $fs->numWonGames,
            "numEqualGames" => $fs->numEqualGames,
            "numLostGames" => $fs->numLostGames,
            "numGoalsShot" => $fs->numGoalsShot,
            "numGoalsGot" => $fs->numGoalsGot,
            "pointsPlus" => $fs->pointsPlus,
            "pointsMinus" => $fs->pointsMinus,
            "pointsPerGame10" => $fs->pointsPerGame10,
            "numGoalsDiffperGame" => $fs->numGoalsDiffperGame,
            "numGoalsShotperGame" => $fs->numGoalsShotperGame,
            "posCriterion" => $fs->posCriterion
        ]);

        $fg = $leagueData->games[0];
        $this->assertEquals([
            "gID" => "4824305",
            "sGID" => "1337956",
            "gNo" => "24400205",
            "live" => false,
            "gToken" => null,
            "gAppid" => "",
            "gDate" => "24.10.21",
            "gWDay" => "So",
            "gTime" => "17:30",
            "gGymnasiumID" => "6196",
            "gGymnasiumNo" => "242115",
            "gGymnasiumPoastal" => "67071",
            "gGymnasiumTown" => "Ludwigshafen",
            "gGymnasiumStreet" => "Hermann-Hesse-Str. 11",
            "gHomeTeam" => "TG Oggersheim",
            "gGuestTeam" => "HSG Eckbachtal 2",
            "gHomeGoals" => 26,
            "gGuestGoals" => 31,
            "gHomeGoals_1" => 15,
            "gGutestGoals_1" => 17,
            "gHomePoints" => 0,
            "gGuestPoints" => 2,
            "gComment" => " ",
            "gGroupsortTxt" => " ",
            "gReferee" => " ",
            "robotextstate" => "unseen"
        ], [
            "gID" => $fg->gID,
            "sGID" => $fg->sGID,
            "gNo" => $fg->gNo,
            "live" => $fg->live,
            "gToken" => $fg->gToken,
            "gAppid" => $fg->gAppid,
            "gDate" => $fg->gDate,
            "gWDay" => $fg->gWDay,
            "gTime" => $fg->gTime,
            "gGymnasiumID" => $fg->gGymnasiumID,
            "gGymnasiumNo" => $fg->gGymnasiumNo,
            "gGymnasiumPoastal" => $fg->gGymnasiumPostal,
            "gGymnasiumTown" => $fg->gGymnasiumTown,
            "gGymnasiumStreet" => $fg->gGymnasiumStreet,
            "gHomeTeam" => $fg->gHomeTeam,
            "gGuestTeam" => $fg->gGuestTeam,
            "gHomeGoals" => $fg->gHomeGoals,
            "gGuestGoals" => $fg->gGuestGoals,
            "gHomeGoals_1" => $fg->gHomeGoals_1,
            "gGutestGoals_1" => $fg->gGuestGoals_1,
            "gHomePoints" => $fg->gHomePoints,
            "gGuestPoints" => $fg->gGuestPoints,
            "gComment" => $fg->gComment,
            "gGroupsortTxt" => $fg->gGroupsortTxt,
            "gReferee" => $fg->gReferee,
            "robotextstate" => $fg->robotextstate
        ]);
    }
}
