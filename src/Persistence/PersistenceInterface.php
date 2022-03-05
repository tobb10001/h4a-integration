<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Persistence;

use Tobb10001\H4aIntegration\Models\LeagueData;
use Tobb10001\H4aIntegration\Models\Team;

interface PersistenceInterface
{
    /**
     * Read what teams are registered at the moment.
     * @return array<Team>
     */
    public function getTeams(): array;

    /**
     * Replace a teams league data.
     * @param int $id The ID of the desired team.
     * @param LeagueData $leagueData The new league data.
     * @return bool True, if the replacement was successful, False otherwise.
     */
    public function replaceLeagueData(int $id, LeagueData $leagueData): bool;
}
