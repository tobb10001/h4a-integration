<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Persistence;

use PHPUnit\Framework\TestCase;

use SQLite3;

/**
 * @covers Tobb10001\H4aIntegration\Persistence\SqliteAdapter
 */
class SqliteAdapterTest extends TestCase
{
    private SQLite3 $sqlite;

    public function setUp(): void
    {
        $this->sqlite = new SQLite3(":memory:");
    }

    private function tableExists(string $tableName): bool
    {
        $res = $this->sqlite->query(
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

    public function testCreateTables()
    {
        // arrange
        $adapter = new SqliteAdapter($this->sqlite);

        // act
        $adapter->createTables("pf_");

        // assert
        $this->assertTrue(self::tableExists("pf_teams"));
        $this->assertTrue(self::tableExists("pf_leaguemetadata"));
        $this->assertTrue(self::tableExists("pf_games"));
        $this->assertTrue(self::tableExists("pf_tabscores"));
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
