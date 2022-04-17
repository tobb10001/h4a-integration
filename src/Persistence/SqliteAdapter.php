<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Persistence;

use Exception;
use SQLite3;
use Tobb10001\H4aIntegration\Exceptions\PersistenceError;
use Tobb10001\H4aIntegration\Models\Game;
use Tobb10001\H4aIntegration\Models\LeagueData;
use Tobb10001\H4aIntegration\Models\LeagueMetadata;
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

    /**
     * {@inheritDoc}
     * @codeCoverageIgnore as long as it is not implemented
     */
    public function replaceLeagueData(int $id, LeagueData $leagueData): bool
    {
        // TODO
        return true;
    }

    /** endregion */

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
        return <<<SQL
			CREATE TABLE ${_ifNotExists} {$this->prefix}leaguemetadata (
				teamid INTEGER NOT NULL,
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				name VARCHAR NOT NULL,
				sname VARCHAR NOT NULL,
				headline1 VARCHAR NOT NULL,
				headline2 VARCHAR NOT NULL,
				actualized VARCHAR NOT NULL,
				repURl VARCHAR NOT NULL,
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
				gWeekDay VARCHAR NOT NULL,
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
				grobotextstate VARCHAR NOT NULL,
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
