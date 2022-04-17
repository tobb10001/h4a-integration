<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Persistence;

use PHPUnit\Framework\TestCase;

use SQLite3;
use SQLite3Result;

use Tobb10001\H4aIntegration\Exceptions\PersistenceError;
use Tobb10001\H4aIntegration\Models\Team;

/**
 * @covers Tobb10001\H4aIntegration\Persistence\SqliteAdapter
 */
class SqliteAdapterTest extends TestCase
{
    private function tableExists(SQLite3 $sqlite, string $tableName): bool
    {
        $res = $sqlite->query(
            // check if table exists
            // https://stackoverflow.com/a/1604121
            "SELECT name FROM sqlite_master WHERE type='table' AND name='${tableName}';"
        );
        if ($res === false) {
            return false;
        }
        // emulate numRows != 0
        // https://www.php.net/manual/en/class.sqlite3result.php#94873
        return (bool) $res->numColumns() && $res->columnType(0) != SQLITE3_NULL;
    }

    /**
     * @uses Tobb10001\H4aIntegration\Models\Team
     */
    public function testGetTeams(): void
    {
        $sqliteMock = $this->createMock(SQLite3::class);
        $resultMock = $this->createMock(SQLite3Result::class);

        $sqliteMock->expects($this->once())
            ->method("query")
            ->with($this->stringStartsWith("SELECT"))
            ->willReturn($resultMock);
        $resultMock->expects($this->exactly(2))
                   ->method("fetchArray")
                   ->willReturn(
                       ["id" => 1, "internalName" => "TeamOne",
                       "identificators" => "Team, One", "leagueUrl" => "leagueUrl",
                       "cupUrl" => "cupUrl"],
                       false
                   );

        $adapter = new SqliteAdapter($sqliteMock);

        $teams = $adapter->getTeams();

        $this->assertTrue(is_array($teams));
        $this->assertEquals(1, count($teams));
        $this->assertInstanceOf(Team::class, $teams[0]);
        $team = $teams[0];
        $this->assertEquals(1, $team->id);
    }

    public function testGetTeamsQueryFail(): void
    {
        $sqliteMock = $this->createMock(SQLite3::class);

        $sqliteMock->expects($this->once())
            ->method("query")
            ->with($this->stringStartsWith("SELECT"))
            ->willReturn(false);
        $sqliteMock->expects($this->once())
                   ->method("lastErrorCode")
                   ->willReturn(0);
        $sqliteMock->expects($this->once())
                   ->method("lastErrorMsg")
               ->willReturn("Some Error.");

        $adapter = new SqliteAdapter($sqliteMock);

        $this->expectException(PersistenceError::class);
        $adapter->getTeams();
    }

    public function testInsertTeam(): void
    {
        // arrange
        $db = new SQLite3(":memory:");

        $team = new Team([
            "internalName" => "TeamOne",
            "identificators" => "TeamOne",
            "leagueUrl" => "leagueUrl",
            "cupUrl" => "cupUrl",
        ]);

        $adapter = new SqliteAdapter($db);
        $adapter->createTables();

        // act
        $result = $adapter->insertTeam($team);

        // assert
        $this->assertTrue($result);

        $teams = $adapter->getTeams();

        $this->assertEquals(1, count($teams));
        $team = $teams[0];
        $this->assertIsInt($team->id);
        $this->assertEquals([
            "internalName" => "TeamOne",
            "identificators" => ["TeamOne"],
            "leagueUrl" => "leagueUrl",
            "cupUrl" => "cupUrl",
        ], [
            "internalName" => $team->internalName,
            "identificators" => $team->identificators,
            "leagueUrl" => $team->leagueUrl,
            "cupUrl" => $team->cupUrl,
        ]);
    }

    public function testCreateTables()
    {
        // arrange
        $sqlite = new SQLite3(":memory:");
        $adapter = new SqliteAdapter($sqlite, "pf_");

        // act
        $adapter->createTables();

        // assert
        $this->assertTrue(self::tableExists($sqlite, "pf_teams"));
        $this->assertTrue(self::tableExists($sqlite, "pf_leaguemetadata"));
        $this->assertTrue(self::tableExists($sqlite, "pf_games"));
        $this->assertTrue(self::tableExists($sqlite, "pf_tabscores"));
    }

    public function testCreateTableRollback(): void
    {
        $mock = $this->createMock(SQLite3::class);

        $mock->expects($this->exactly(3))
             ->method("exec")
             ->withConsecutive(
                 ["BEGIN;"],
                 [$this->stringContains("CREATE TABLE")],
                 ["ROLLBACK;"]
             )
             ->willReturn(true, false, true);

        $adapter = new SqliteAdapter($mock);
        $this->assertFalse($adapter->createTables());
    }
}
