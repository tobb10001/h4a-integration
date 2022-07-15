<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Persistence;

use Exception;
use SQLite3;
use Tobb10001\H4aIntegration\Exceptions\PersistenceError;
use Tobb10001\H4aIntegration\Models\Game;
use Tobb10001\H4aIntegration\Models\LeagueData;
use Tobb10001\H4aIntegration\Models\LeagueMetadata;
use Tobb10001\H4aIntegration\Models\LeagueType;
use Tobb10001\H4aIntegration\Models\TabScore;
use Tobb10001\H4aIntegration\Models\Team;

/**
 * Interface for a Sqlite-Database. Allows managing teams, querying data
 * and updating it.
 */
class SqliteAdapter implements PersistenceInterface
{
    public const IF_NOT_EXISTS = 0x1;

    private SQLite3 $db;
    private string $prefix;

    /**
     * Dependency injection with the SQLite3 object.
     *
     * The SQLite3 object is expected to be in an open state every time a
     * method of SqliteAdapter is called. Also, it is expected that exceptions
     * are disabled, since the return values of SQLite3-Methods are used to
     * cntrol transactions.
     * @param SQLite3 $db
     * @param string $prefix Prefix to prepend to table names.
     */
    public function __construct(SQLite3 $db, string $prefix = "")
    {
        $this->db = $db;
        $this->prefix = $prefix;
    }

    /** region UpdaterInterface */

    /**
     * {@inheritDoc}
     */
    public function getTeams(): array
    {
        $res = $this->db->query(
            "SELECT * FROM {$this->prefix}teams;"
        );

        if ($res === false) {
            throw new PersistenceError(
                "Teams could not be fetched: "
                    . "{$this->db->lastErrorCode()} {$this->db->lastErrorMsg()}"
            );
        }

        $teamArrs = [];
        $currTeam = null;
        while ($currTeam = $res->fetchArray(SQLITE3_ASSOC)) {
            $teamArrs[] = $currTeam;
        }

        $teamObjs = array_map(function ($item) {
            return new Team($item);
        }, $teamArrs);
        return $teamObjs;
    }


    /**
     * {@inheritDoc}
     */
    public function replaceLeagueData(int $teamid, LeagueData $leagueData): bool
    {
        $exceptionState = $this->db->enableExceptions(true);

        if (is_null($leagueData->type)) {
            throw new PersistenceError(
                "Cannot save league data, that does not have a type."
            );
        }

        try {
            $this->db->exec("BEGIN;");

            // delete old data
            $stmt = $this->db->prepare(
                "DELETE FROM {$this->prefix}leaguemetadata WHERE teamid = :teamid AND type = :type"
            );
            if ($stmt === false) {
                throw new PersistenceError(
                    "Could not prepare statement to delete old league data:"
                    . " {$this->db->lastErrorCode()} {$this->db->lastErrorMsg()}"
                );
            }
            $stmt->bindValue("teamid", $teamid);
            $stmt->bindValue("type", $leagueData->type->value);
            $stmt->execute();

            // insert metadata
            $metadataId = $this->insertMetadata($teamid, $leagueData->type, $leagueData->metadata);

            // insert games
            foreach ($leagueData->games as $game) {
                $this->insertGame($metadataId, $game);
            }

            // insert games
            foreach ($leagueData->table as $tabScore) {
                $this->insertTabScore($metadataId, $tabScore);
            }

            $this->db->exec("COMMIT;");
        } catch (Exception $e) {
            $this->db->exec("ROLLBACK;");
            throw new PersistenceError(
                "Could not replace league data for Team {$teamid}: {$e->getMessage()}",
                0,
                $e
            );
        }

        $this->db->enableExceptions($exceptionState);

        return true;
    }
    /** endregion */

    /**
     * Insert a metadata into the database.
     * @param LeagueMetadata $leagueMetadata The metadata to insert.
     * @return int The ID of the inserted metadata.
     */
    private function insertMetadata(int $teamid, LeagueType $type, LeagueMetadata $leagueMetadata): int
    {
        $stmt = $this->db->prepare(
            <<<SQL
                INSERT INTO {$this->prefix}leaguemetadata (
                    teamid,
                    `type`,
                    name,
                    sname,
                    headline1,
                    headline2,
                    actualized,
                    repUrl,
                    scoreShownPerGame
                ) VALUES (
                    :teamid,
                    :type,
                    :name,
                    :sname,
                    :headline1,
                    :headline2,
                    :actualized,
                    :repUrl,
                    :scoreShownPerGame
                );
            SQL
        );

        if ($stmt === false) {
            throw new PersistenceError(
                "Could not prepare insert metadata query:"
                    . " {$this->db->lastErrorCode()} {$this->db->lastErrorMsg()}"
            );
        }

        $stmt->bindValue("teamid", $teamid);
        $stmt->bindValue("type", $type->value);
        $stmt->bindValue("name", $leagueMetadata->name);
        $stmt->bindValue("sname", $leagueMetadata->sname);
        $stmt->bindValue("headline1", $leagueMetadata->headline1);
        $stmt->bindValue("headline2", $leagueMetadata->headline2);
        $stmt->bindValue("actualized", $leagueMetadata->actualized);
        $stmt->bindValue("repUrl", $leagueMetadata->repURL);
        $stmt->bindValue("scoreShownPerGame", $leagueMetadata->scoreShowDataPerGame);
        $stmt->execute();

        return $this->db->lastInsertRowID();
    }

    /**
     * Insert a game into the database.
     * @param Game $game The game to insert.
     * @return bool True, if the insertion was succsessful, false otherwise.
     */
    private function insertGame(int $metadataId, Game $game): bool
    {
        $stmt = $this->db->prepare(<<< SQL
            INSERT INTO {$this->prefix}games (
                metadataid,
                gID,
                sGID,
                gNo,
                live,
                gToken,
                gAppid,
                gDate,
                gWDay,
                gTime,
                gGymnasiumID,
                gGymnasiumNo,
                gGymnasiumName,
                gGymnasiumPostal,
                gGymnasiumTown,
                gGymnasiumStreet,
                gHomeTeam,
                gGuestTeam,
                gHomeGoals,
                gGuestGoals,
                gHomeGoals_1,
                gGuestGoals_1,
                gHomePoints,
                gGuestPoints,
                gComment,
                gGroupsortTxt,
                gReferee,
                robotextstate
            ) VALUES (
                :metadataid,
                :gID,
                :sGID,
                :gNo,
                :live,
                :gToken,
                :gAppid,
                :gDate,
                :gWDay,
                :gTime,
                :gGymnasiumID,
                :gGymnasiumNo,
                :gGymnasiumName,
                :gGymnasiumPostal,
                :gGymnasiumTown,
                :gGymnasiumStreet,
                :gHomeTeam,
                :gGuestTeam,
                :gHomeGoals,
                :gGuestGoals,
                :gHomeGoals_1,
                :gGuestGoals_1,
                :gHomePoints,
                :gGuestPoints,
                :gComment,
                :gGroupsortTxt,
                :gReferee,
                :robotextstate
            );
        SQL);

        if ($stmt === false) {
            throw new PersistenceError(
                "Could not prepare insert game query:"
                . " {$this->db->lastErrorCode()} {$this->db->lastErrorMsg()}"
            );
        }
        $stmt->bindValue("metadataid", $metadataId);
        $stmt->bindValue("gID", $game->gID);
        $stmt->bindValue("sGID", $game->sGID);
        $stmt->bindValue("gNo", $game->gNo);
        $stmt->bindValue("live", $game->live);
        $stmt->bindValue("gToken", $game->gToken);
        $stmt->bindValue("gAppid", $game->gAppid);
        $stmt->bindValue("gDate", $game->gDate);
        $stmt->bindValue("gWDay", $game->gWDay);
        $stmt->bindValue("gTime", $game->gTime);
        $stmt->bindValue("gGymnasiumID", $game->gGymnasiumID);
        $stmt->bindValue("gGymnasiumNo", $game->gGymnasiumNo);
        $stmt->bindValue("gGymnasiumName", $game->gGymnasiumName);
        $stmt->bindValue("gGymnasiumPostal", $game->gGymnasiumPostal);
        $stmt->bindValue("gGymnasiumTown", $game->gGymnasiumTown);
        $stmt->bindValue("gGymnasiumStreet", $game->gGymnasiumStreet);
        $stmt->bindValue("gHomeTeam", $game->gHomeTeam);
        $stmt->bindValue("gGuestTeam", $game->gGuestTeam);
        $stmt->bindValue("gHomeGoals", $game->gHomeGoals);
        $stmt->bindValue("gGuestGoals", $game->gGuestGoals);
        $stmt->bindValue("gHomeGoals_1", $game->gHomeGoals_1);
        $stmt->bindValue("gGuestGoals_1", $game->gGuestGoals_1);
        $stmt->bindValue("gHomePoints", $game->gHomePoints);
        $stmt->bindValue("gGuestPoints", $game->gGuestPoints);
        $stmt->bindValue("gComment", $game->gComment);
        $stmt->bindValue("gGroupsortTxt", $game->gGroupsortTxt);
        $stmt->bindValue("gReferee", $game->gReferee);
        $stmt->bindValue("robotextstate", $game->robotextstate);

        return (bool) $stmt->execute();
    }

    /**
     * Insert a tabscore into the database.
     * @param TabScore $tabScore The TabScore to insert.
     * @return bool True, if the insertion was succsessful, false otherwise.
     */
    private function insertTabScore(int $metadataId, TabScore $tabScore): bool
    {
        $stmt = $this->db->prepare(<<< SQL
            INSERT INTO {$this->prefix}tabscores (
                metadataid,
                tabScore,
                tabTeamID,
                tabTeamname,
                liveTeam,
                numPlayedGames,
                numWonGames,
                numEqualGames,
                numLostGames,
                numGoalsShot,
                numGoalsGot,
                pointsPlus,
                pointsMinus,
                pointsPerGame10,
                numGoalsDiffperGame,
                numGoalsShotperGame,
                posCriterion
            ) VALUES (
                :metadataid,
                :tabScore,
                :tabTeamID,
                :tabTeamname,
                :liveTeam,
                :numPlayedGames,
                :numWonGames,
                :numEqualGames,
                :numLostGames,
                :numGoalsShot,
                :numGoalsGot,
                :pointsPlus,
                :pointsMinus,
                :pointsPerGame10,
                :numGoalsDiffperGame,
                :numGoalsShotperGame,
                :posCriterion
            );
        SQL);

        if ($stmt === false) {
            throw new PersistenceError(
                "Could not prepare insert tabscore query:"
                . " {$this->db->lastErrorCode()} {$this->db->lastErrorMsg()}"
            );
        }

        $stmt->bindValue("metadataid", $metadataId);
        $stmt->bindValue("tabScore", $tabScore->tabScore);
        $stmt->bindValue("tabTeamID", $tabScore->tabTeamID);
        $stmt->bindValue("tabTeamname", $tabScore->tabTeamname);
        $stmt->bindValue("liveTeam", $tabScore->liveTeam);
        $stmt->bindValue("numPlayedGames", $tabScore->numPlayedGames);
        $stmt->bindValue("numWonGames", $tabScore->numWonGames);
        $stmt->bindValue("numEqualGames", $tabScore->numEqualGames);
        $stmt->bindValue("numLostGames", $tabScore->numLostGames);
        $stmt->bindValue("numGoalsShot", $tabScore->numGoalsShot);
        $stmt->bindValue("numGoalsGot", $tabScore->numGoalsGot);
        $stmt->bindValue("pointsPlus", $tabScore->pointsPlus);
        $stmt->bindValue("pointsMinus", $tabScore->pointsMinus);
        $stmt->bindValue("pointsPerGame10", $tabScore->pointsPerGame10);
        $stmt->bindValue("numGoalsDiffperGame", $tabScore->numGoalsDiffperGame);
        $stmt->bindValue("numGoalsShotperGame", $tabScore->numGoalsShotperGame);
        $stmt->bindValue("posCriterion", $tabScore->posCriterion);

        return (bool) $stmt->execute();
    }

    /**
     * Insert a team into the database.
     * @param Team $team The team to insert.
     * @return bool True, if the insertion was succsessful, false otherwise.
     */
    public function insertTeam(Team $team): bool
    {
        $sql = <<<SQL
            INSERT INTO {$this->prefix}teams (
                internalName,
                identificators,
                leagueUrl,
                cupUrl
            ) VALUES (
                :internalName,
                :identificators,
                :leagueUrl,
                :cupUrl
            );
SQL;
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            throw new PersistenceError(
                "Could not prepare statement: "
                . "{$this->db->lastErrorCode()} {$this->db->lastErrorMsg()}"
            );
        }

        $stmt->bindValue("internalName", $team->internalName);
        $stmt->bindValue("identificators", $team->identificatorStr());
        $stmt->bindValue("leagueUrl", $team->leagueUrl);
        $stmt->bindValue("cupUrl", $team->cupUrl);

        return (bool) $stmt->execute();
    }

    /** region Table Creation */
    /**
     * Construct the "CREATE TABLE" query for the teams table.
     * @param bool $ifNotExists Whether to use "IF NOT EXISTS" for safety.
     */
    private function queryCreateTableTeams(bool $ifNotExists): string
    {
        $_ifNotExists = $ifNotExists ? "IF NOT EXISTS" : "";
        return <<<SQL
			CREATE TABLE ${_ifNotExists} {$this->prefix}teams (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				internalName VARCHAR NOT NULL,
				identificators VARCHAR NOT NULL,
				leagueUrl VARCHAR NULL,
				cupUrl VARCHAR NULL
			);
SQL;
    }

    /**
     * Create the teams table.
     * @param bool $ifNotExists Whether to use "IF NOT EXISTS" for safety.
     */
    private function createTableTeams(bool $ifNotExists): bool
    {
        return $this->db->exec(self::queryCreateTableTeams($ifNotExists));
    }

    /**
     * Construct the "CREATE TABLE" query for the league data table.
     * @param bool $ifNotExists Whether to use "IF NOT EXISTS" for safety.
     */
    private function queryCreateTableLeagueMetadata(bool $ifNotExists): string
    {
        $_ifNotExists = $ifNotExists ? "IF NOT EXISTS" : "";
        $types = implode(',', array_map(function ($item) {
            return "'" . $item->value . "'";
        }, LeagueType::cases()));
        return <<<SQL
			CREATE TABLE ${_ifNotExists} {$this->prefix}leaguemetadata (
				teamid INTEGER NOT NULL,
                `type` TEXT CHECK( `type` in ({$types})) NOT NULL,
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				name VARCHAR NOT NULL,
				sname VARCHAR NOT NULL,
				headline1 VARCHAR NOT NULL,
				headline2 VARCHAR NOT NULL,
				actualized VARCHAR NOT NULL,
				repUrl VARCHAR NOT NULL,
				scoreShownPerGame NOT NULL,
				CONSTRAINT fk_team
					FOREIGN KEY (teamid) REFERENCES {$this->prefix}team(id)
					ON UPDATE CASCADE ON DELETE CASCADE
			);
SQL;
    }

    /**
     * Create the metadata table.
     * @param bool $ifNotExists Whether to use "IF NOT EXISTS" for safety.
     */
    private function createTableLeagueMetadata(bool $ifNotExists): bool
    {
        return $this->db->exec(self::queryCreateTableLeagueMetadata($ifNotExists));
    }

    /**
     * Construct the "CREATE TABLE" query for the games table.
     * @param bool $ifNotExists Whether to use "IF NOT EXISTS" for safety.
     */
    private function queryCreateTableGames(bool $ifNotExists): string
    {
        $_ifNotExists = $ifNotExists ? "IF NOT EXISTS" : "";
        return <<<SQL
			CREATE TABLE ${_ifNotExists} {$this->prefix}games (
				metadataid INTEGER NOT NULL,
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				gID VARCHAR NOT NULL,
				sGID VARCHAR NULL DEFAULT NULL,
				gNo VARCHAR NOT NULL,
				live BOOLEAN NOT NULL,
				gToken VARCHAR NULL DEFAULT NULL,
				gAppid VARCHAR NOT NULL,
				gDate VARCHAR NOT NULL,
				gWDay VARCHAR NOT NULL,
				gTime VARCHAR NOT NULL,
				gGymnasiumID VARCHAR NOT NULL,
				gGymnasiumNo VARCHAR NOT NULL,
				gGymnasiumName VARCHAR NOT NULL,
				gGymnasiumPostal VARCHAR NOT NULL,
				gGymnasiumTown VARCHAR NOT NULL,
				gGymnasiumStreet VARCHAR NOT NULL,
				gHomeTeam VARCHAR NOT NULL,
				gGuestTeam VARCHAR NOT NULL,
				gHomeGoals INT NULL DEFAULT NULL,
				gGuestGoals INT NULL DEFAULT NULL,
				gHomeGoals_1 INT NULL DEFAULT NULL,
				gGuestGoals_1 INT NULL DEFAULT NULL,
				gHomePoints INT NULL DEFAULT NULL,
				gGuestPoints INT NULL DEFAULT NULL,
				gComment VARCHAR NOT NULL,
				gGroupsortTxt VARCHAR NOT NULL,
				gReferee VARCHAR NOT NULL,
				robotextstate VARCHAR NOT NULL,
				CONSTRAINT fk_metadata
					FOREIGN KEY (metadataid) REFERENCES {$this->prefix}metadata(id)
					ON UPDATE CASCADE ON DELETE CASCADE
			);
SQL;
    }

    /**
     * Create the games table.
     * @param bool $ifNotExists Whether to use "IF NOT EXISTS" for safety.
     */
    private function createTableGames(bool $ifNotExists): bool
    {
        return $this->db->exec(self::queryCreateTableGames($ifNotExists));
    }

    /**
     * Construct the "CREATE TABLE" query for the tabscores table.
     * @param bool $ifNotExists Whether to use "IF NOT EXISTS" for safety.
     */
    private function queryCreateTableTabScores(bool $ifNotExists): string
    {
        $_ifNotExists = $ifNotExists ? "IF NOT EXISTS" : "";
        return <<<SQL
			CREATE TABLE ${_ifNotExists} {$this->prefix}tabscores (
				metadataid INTEGER NOT NULL,
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
				tabScore INT NOT NULL,
				tabTeamID VARCHAR NOT NULL,
				tabTeamname VARCHAR NOT NULL,
				liveTeam BOOLEAN NOT NULL,
				numPlayedGames INT NOT NULL,
				numWonGames INT NOT NULL,
				numEqualGames INT NOT NULL,
				numLostGames INT NOT NULL,
				numGoalsShot INT NOT NULL,
				numGoalsGot INT NOT NULL,
				pointsPlus INT NOT NULL,
				pointsMinus INT NOT NULL,
				pointsPerGame10 VARCHAR NOT NULL,
				numGoalsDiffperGame VARCHAR NOT NULL,
				numGoalsShotperGame VARCHAR NOT NULL,
				posCriterion VARCHAR NOT NULL,
				CONSTRAINT fk_metadata
					FOREIGN KEY (metadataid) REFERENCES {$this->prefix}metadata(id)
					ON UPDATE CASCADE ON DELETE CASCADE
			);
SQL;
    }

    /**
     * Create the games table.
     * @param bool $ifNotExists Whether to use "IF NOT EXISTS" for safety.
     */
    private function createTableTabScores(bool $ifNotExists): bool
    {
        return $this->db->exec(self::queryCreateTableTabScores($ifNotExists));
    }

    /**
     * Create the database tables, that are needed to store the desired data.
     * @param int $flags
     */
    public function createTables(int $flags = self::IF_NOT_EXISTS): bool
    {
        $ifNotExists = (bool) ($flags & self::IF_NOT_EXISTS);

        $result = true;

        $this->db->exec("BEGIN;");
        // chained konjunction
        // if one of the exec()-calls (table creations) fail, the others won't be attempted
        $result = $this->createTableTeams($ifNotExists)
        && $this->createTableLeagueMetadata($ifNotExists)
        && $this->createTableGames($ifNotExists)
        && $this->createTableTabScores($ifNotExists);

        if ($result) {
            $this->db->exec("COMMIT;");
        } else {
            $this->db->exec("ROLLBACK;");
        }

        return $result;
    }
    /** endregion */
}
