<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

/**
 * Represents an API response as returned by the League-Endpoint.
 */
class LeagueData
{
    /** @var LeagueMetadata $metadata The league's metadata. */
    public LeagueMetadata $metadata;

    /** @var Table $table The table. */
    public Table $table;
    /** @var GameSchedule $games The games (past and future). */
    public GameSchedule $games;

    /**
     * @param array<mixed> $input
     * @param Table $table
     * @param GameSchedule $games
     */
    public function __construct(array $input, Table $table, GameSchedule $games)
    {
        $this->metadata = new LeagueMetadata($input);

        $this->table = $table;
        $this->games = $games;
    }

    /**
     * Construct an object from JSON: The response of the league endpoint.
     * @param array<mixed> $jsonAssoc
     * @return self
     */
    public static function fromJson(array $jsonAssoc): self
    {
        $jsonAssoc = $jsonAssoc[0];

        $metadata = $jsonAssoc["head"];
        $table = Table::fromJson($jsonAssoc["content"]["score"]);
        $games = GameSchedule::fromJson($jsonAssoc["content"]["futureGames"]);
        return new self($metadata, $table, $games);
    }
}
