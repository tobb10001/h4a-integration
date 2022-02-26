<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegrator\Persistance;

use Tobb10001\H4aIntegration\Models\Game;
use Tobb10001\H4aIntegration\Models\LeagueMetadata;
use Tobb10001\H4aIntegration\Models\Table;
use Tobb10001\H4aIntegration\Exceptions\UnknownFilterException;

interface ConsumerInterface
{
    /**
     * Retrieve a table, that belongs to a specific team.
     * @param int $id The ID of the desired team.
     * @return Table
     */
    public function getTable(int $id): Table;

    /**
     * Retrieve league metadata, that belongs to a specific team.
     * @param int $id The ID of the desired team.
     * @return LeagueMetadata
     */
    public function getLeagueMetadata(int $id): LeagueMetadata;


    /**
     * Retrieve games.
     * $filters is an array of objects, that can be used to filter the games.
     *
     * There is no restriction of what a filter looks like.
     * - When creating a filter, it should be obvious what it is meant to do.
     * - Each filter should be a distinct class, s.th. an implementation of
     *      getGames() can use `instanceof` to identify them.
     *  - When implementing this function, you can impement all filters that
     *      you need. Common filters live in .\GameFilters.
     *  - If the array contains a filter, that the function does not know how
     *      to use, it should throw an UnknownFilterException.
     * @param array<mixed> $filters Restrictions which games to select.
     * @return array<Game>
     * @throws UnknownFilterException
     */
    public function getGames(array $filters): array;
}
