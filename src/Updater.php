<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration;

use Tobb10001\H4aIntegration\Exceptions\HttpException;
use Tobb10001\H4aIntegration\Exceptions\ProgrammingError;
use Tobb10001\H4aIntegration\Models\LeagueData;
use Tobb10001\H4aIntegration\Models\Team;
use Tobb10001\H4aIntegration\Persistence\PersistenceInterface;
use Tobb10001\H4aIntegration\Util\HttpClient;

/**
 * Update the the teams registered in the given persistence.
 */
class Updater
{
    private PersistenceInterface $pi;
    private HttpClient $hc;

    /**
     * @param PersistenceInterface $pi
     * @param HttpClient $hc If $hc === null, an HttpClient-object is
     * instanciated.
     */
    public function __construct(PersistenceInterface $pi, ?HttpClient $hc = null)
    {
        $this->pi = $pi;
        $this->hc = $hc ?? new HttpClient();
    }

    /**
     * Update all teams in the databse.
     * This function is supposed to be run by a cronjob.
     */
    public function update(): void
    {
        $teams = $this->pi->getTeams();

        foreach ($teams as $team) {
            if (is_null($team->id)) {
                trigger_error(
                    self::class . "->update() got a team without an ID:"
                        . $team->internalName . "Ignoring the team and continuing.",
                    E_USER_WARNING
                );
                continue;
            }
            $this->updateTeam($team);
        }
    }

    /**
     * Use the associated HttpClient to download the league data for the
     * desired team and convert it to a LeagueData object.
     */
    private function leagueDataFor(Team $team): LeagueData
    {
        if (is_null($team->leagueUrl)) {
            throw new ProgrammingError(
                __METHOD__ . " was called with a team without leagueUrl: {$team->id}: {$team->internalName}."
            );
        }
        $json = $this->hc->getJson($team->leagueUrl);
        return LeagueData::fromJson($json);
    }

    /**
     * Update a single team in the database.
     */
    private function updateTeam(Team $team): void
    {
        if (is_null($team->id)) {
            throw new ProgrammingError(
                __METHOD__ . " was called with a team without ID: {$team->internalName}"
            );
        }
        if (!is_null($team->leagueUrl)) {
            try {
                $leagueData = $this->leagueDataFor($team);
                $this->pi->replaceLeagueData($team->id, $leagueData);
            } catch (HttpException) {
                trigger_error(
                    "Leauge for Team {$team->id} ({$team->internalName}) could"
                    . " not be updated, due to a failed request. Ignoring."
                );
            }
        }
    }
}
