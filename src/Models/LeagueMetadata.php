<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

class LeagueMetadata
{
    /** @var string $name The name of the league. */
    public string $name;
    /** @var string $sname The short version of the name of the league. */
    public string $sname;
    public string $headline1;
    public string $headline2;
    /**
     * @var string $actualized Human readable (german) string telling the last
     * time the data was actualized.
     */
    public string $actualized;
    /**
     * @var string $repURL URL to prepend to Game::$sGID to construct the
     * report URL.
     */
    public string $repURL;
    public bool $scoreShownDataPerGame;

    /**
     * @param array<mixed> $input
     */
    public function __construct(array $input)
    {
        $this->name = $input["name"];
        $this->sname = $input["sname"];
        $this->headline1 = $input["headline1"];
        $this->headline2 = $input["headline2"];
        $this->actualized = $input["actualized"];
        $this->repURL = $input["repURL"];
        $this->scoreShownDataPerGame = $input["scoreShownDataPerGame"];
    }
}
