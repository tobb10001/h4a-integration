<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration;

use Tobb10001\H4aIntegration\Exceptions\HttpException;
use Tobb10001\H4aIntegration\Exceptions\ProgrammingError;
use Tobb10001\H4aIntegration\Models\LeagueData;
use Tobb10001\H4aIntegration\Models\LeagueType;
use Tobb10001\H4aIntegration\Models\Team;
use Tobb10001\H4aIntegration\Persistence\PersistenceInterface;
use Tobb10001\H4aIntegration\Util\CurlHttpClient;
use Tobb10001\H4aIntegration\Util\HttpClientInterface;
use Tobb10001\H4aIntegration\Util\UpdateResultlet;

/**
 * Update the the teams registered in the given persistence.
 */
class Updater
{
    private PersistenceInterface $pi;
    private HttpClientInterface $hc;

    /**
     * @param PersistenceInterface $pi
     * @param HttpClientInterface $hc If $hc === null, an HttpClient-object is
     * instanciated.
     */
    public function __construct(PersistenceInterface $pi, ?HttpClientInterface $hc = null)
    {
        $this->pi = $pi;
        $this->hc = $hc ?? new CurlHttpClient();
    }

    /**
     * Update all teams in the databse.
     * This function is supposed to be run by a cronjob.
     * @return UpdateResult A mapping of Team-IDs to success-states.
     */
    public function update(): UpdateResult
    {
        $teams = $this->pi->getTeams();

        $result = new UpdateResult();

        foreach ($teams as $team) {
            if (is_null($team->id)) {
                trigger_error(
                    self::class . "->update() got a team without an ID:"
                        . $team->internalName . "Ignoring the team and continuing.",
                    E_USER_WARNING
                );
                $reslet = new UpdateResultlet();
                $reslet->leagueFailure("Team has no ID.", UpdateResultlet::NOT_TRIED);
                $reslet->cupFailure("Team has no ID.", UpdateResultlet::NOT_TRIED);
                continue;
            }

            $result[$team->id] = $this->updateTeam($team);
        }

        return $result;
    }

    /**
     * Use the associated HttpClient to download the league data for the
     * desired team and convert it to a LeagueData object.
     */
    private function dataFromUrl(string $url): LeagueData
    {
        return LeagueData::fromJson($this->hc->getJson($url));
    }

    /**
     * Update a single team in the database.
     * @return UpdateResultlet
     */
    private function updateTeam(Team $team): UpdateResultlet
    {
        $result = new UpdateResultlet();

        if (is_null($team->id)) {
            throw new ProgrammingError(
                "Cannot update a team that does not have an ID."
            );
        }

        if (!is_null($team->leagueUrl)) {
            try {
                $leagueData = $this->dataFromUrl($team->apiUrlLeague());
                $leagueData->type = LeagueType::League;
                $this->pi->replaceLeagueData($team->id, $leagueData);
                $result->leagueStatus = UpdateResultlet::SUCCESS;
            } catch (HttpException $e) {
                $result->leagueStatus = UpdateResultlet::HTTP_EXCEPTION;
                $result->leagueErrorMessage = "Exception: HttpException: {$e->getMessage()}";
            }
        }

        if (!is_null($team->cupUrl)) {
            try {
                $leagueData = $this->dataFromUrl($team->apiUrlCup());
                $leagueData->type = LeagueType::Cup;
                $this->pi->replaceLeagueData($team->id, $leagueData);
                $result->leagueStatus = UpdateResultlet::SUCCESS;
            } catch (HttpException $e) {
                $result->leagueStatus = UpdateResultlet::HTTP_EXCEPTION;
                $result->leagueErrorMessage = "Exception: HttpException: {$e->getMessage()}";
            }
        }

        return $result;
    }
}
