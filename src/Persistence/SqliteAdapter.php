<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Persistence;

use SQLite3;
use Tobb10001\H4aIntegration\Models\LeagueData;

/**
 * Interface for a Sqlite-Database. Allows managing teams, querying data
 * and updating it.
 */
class SqliteAdapter implements UpdaterInterface
{
    public const IF_NOT_EXISTS = 0x1;

    private SQLite3 $db;

    /**
     * Dependency injection with the SQLite3 object.
     *
     * The SQLite3 object is expected to be in an open state every time a
     * method of SqliteAdapter is called. Also, it is expected that exceptions
     * are disabled, since the return values of SQLite3-Methods are used to
     * control transactions.
     * @param SQLite3 $db
     */
    public function __construct(SQLite3 $db)
    {
        $this->db = $db;
    }

    /** region UpdaterInterface */

    /**
     * {@inheritDoc}
     */
    public function getTeams(): array
    {
        // TODO
        return [];
    }

    /**
     * {@inheritDoc}
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
     * @param string $prefix Prefix to prepend to the table name.
     * @param bool $ifNotExists Whether to use "IF NOT EXISTS" for safety.
     */
    private static function queryCreateTableTeams(string $prefix, bool $ifNotExists): string
    {
        $_ifNotExists = $ifNotExists ? "IF NOT EXISTS" : "";
        return <<<SQL
			CREATE TABLE ${prefix}teams ${_ifNotExists} (
				id INTEGER AUTO_INCREMENT PRIMARY KEY,
				internalName VARCHAR NOT NULL,
				identificators VARCHAR NOT NULL,
				leagueUrl VARCHAR NULL,
				cupUrl VARCHAR NULL,
			);
SQL;
    }

    /**
     * Construct the "CREATE TABLE" query for the league data table.
     * @param string $prefix Prefix to prepend to the table name.
     * @param bool $ifNotExists Whether to use "IF NOT EXISTS" for safety.
     */
    private static function queryCreateTableLeagueMetadata(string $prefix, bool $ifNotExists): string
    {
        $_ifNotExists = $ifNotExists ? "IF NOT EXISTS" : "";
        return <<<SQL
			CREATE TABLE ${prefix}leaguemetadata ${_ifNotExists} (
				teamid INTEGER NOT NULL,
				id INTEGER PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR NOT NULL,
				sname VARCHAR NOT NULL,
				headline1 VARCHAR NOT NULL,
				headline2 VARCHAR NOT NULL,
				actualized VARCHAR NOT NULL,
				repURl VARCHAR NOT NULL,
				scoreShownPerGame NOT NULL,
				CONSTRAINT fk_team
					FOREIGN KEY (teamid) REFERENCES ${prefix}team(id)
					ON UPDATE CASCADE ON DELETE CASCADE
			);
SQL;
    }

    /**
     * Construct the "CREATE TABLE" query for the games table.
     * @param string $prefix Prefix to prepend to the table name.
     * @param bool $ifNotExists Whether to use "IF NOT EXISTS" for safety.
     */
    private static function queryCreateTableGames(string $prefix, bool $ifNotExists): string
    {
        $_ifNotExists = $ifNotExists ? "IF NOT EXISTS" : "";
        return <<<SQL
			CREATE TABLE ${prefix}games ${_ifNotExists} (
				metadataid INTEGER NOT NULL,
				id INTEGER PRIMARY KEY AUTO_INCREMENT,
				gID VARCHAR NOT NULL,
				gNo VARCHAR NOT NULL,
				live BOOLEAN NOT NULL,
				gToken VARCHAR NOT NULL,
				gAppid VARCHAR NOT NULL,
				gDate VARCHAR NOT NULL,
				gWeekDay VARCHAR NOT NULL,
				gTime VARCHAR NOT NULL,
				gGymnasiumID VARCHAR NOT NULL,
				gGymnasiumNo VARCHAR NOT NULL,
				gGymnasiumName VARCHAR NOT NULL,
				gGymnasiumPoastal VARCHAR NOT NULL,
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
					FOREIGN KEY (teamid) REFERENCES ${prefix}metadata(id)
					ON UPDATE CASCADE ON DELETE CASCADE
			);
SQL;
    }

    /**
     * Construct the "CREATE TABLE" query for the tabscores table.
     * @param string $prefix Prefix to prepend to the table name.
     * @param bool $ifNotExists Whether to use "IF NOT EXISTS" for safety.
     */
    private static function queryCreateTableTabScores(string $prefix, bool $ifNotExists): string
    {
        $_ifNotExists = $ifNotExists ? "IF NOT EXISTS" : "";
        return <<<SQL
			CREATE TABLE ${prefix}games ${_ifNotExists} (
				metadataid INTEGER NOT NULL,
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
					FOREIGN KEY (teamid) REFERENCES ${prefix}metadata(id)
					ON UPDATE CASCADE ON DELETE CASCADE
			);
SQL;
    }

    /**
     * Create the database tables, that are needed to store the desired data.
     * @param string $prefix Prefix to prepend to all tables.
     * @param int $flags
     */
    public function createTables(string $prefix = "", int $flags = self::IF_NOT_EXISTS): bool
    {
        $ifNotExists = (bool) ($flags & self::IF_NOT_EXISTS);

        $result = true;

        $this->db->exec("BEGIN;");
        // chained konjunction
        // if one of the exec()-calls (table creations) fail, the others won't be attempted
        $result = $this->db->exec(
            self::queryCreateTableTeams($prefix, $ifNotExists)
        )
        && $this->db->exec(
            self::queryCreateTableLeagueMetadata($prefix, $ifNotExists)
        )
        && $this->db->exec(
            self::queryCreateTableGames($prefix, $ifNotExists)
        )
        && $this->db->exec(
            self::queryCreateTableTabScores($prefix, $ifNotExists)
        );

        if ($result) {
            $this->db->exec("COMMIT;");
        } else {
            $this->db->exec("ROLLBACK;");
        }

        return $result;
    }
    /** endregion */
}
