<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

/**
 * Represents a record in the league's table.
 * Oriented at the ["score"]-Json.
 */
class TabScore
{
    /** @var int $tabScore The team's position. */
    public int $tabScore;
    public string $tabTeamID;
    /** @var string $tabTeamname The name of the team. */
    public string $tabTeamname;
    public bool $liveTeam;
    /** @var int $numPlayedGames The number of played games. */
    public int $numPlayedGames;
    /** @var int $numWonGames The number of won games. */
    public int $numWonGames;
    /** @var int $numEqualGames The number of tied games. */
    public int $numEqualGames;
    /** @var int $numLostGames The number of lost games. */
    public int $numLostGames;
    /** @var int $numGoalsShot The number of goals shot. */
    public int $numGoalsShot;
    /** @var int $numGoalsGot The number of goals allowed. */
    public int $numGoalsGot;
    /** @var int $popublic intsPlus The number of won popublic ints. */
    public int $pointsPlus;
    /** @var int $popublic intsMinus The number of lost popublic ints. */
    public int $pointsMinus;

    public string $pointsPerGame10;
    public string $numGoalsDiffperGame;
    public string $numGoalsShotperGame;
    public string $posCriterion;

    /**
     * @param array<mixed> $input
     */
    public function __construct(array $input)
    {
        $this->tabScore = $input["tabScore"];
        $this->tabTeamID = $input["tabTeamID"];
        $this->tabTeamname = $input["tabTeamname"];
        $this->liveTeam = $input["liveTeam"];
        $this->numPlayedGames = $input["numPlayedGames"];
        $this->numWonGames = $input["numWonGames"];
        $this->numEqualGames = $input["numEqualGames"];
        $this->numLostGames = $input["numLostGames"];
        $this->numGoalsShot = $input["numGoalsShot"];
        $this->numGoalsGot = $input["numGoalsGot"];
        $this->pointsPlus = $input["pointsPlus"];
        $this->pointsMinus = $input["pointsMinus"];

        $this->pointsPerGame10 = $input["pointsPerGame10"];
        $this->numGoalsDiffperGame = $input["numGoalsDiffperGame"];
        $this->numGoalsShotperGame = $input["numGoalsShotperGame"];
        $this->posCriterion = $input["posCriterion"];
    }
}
