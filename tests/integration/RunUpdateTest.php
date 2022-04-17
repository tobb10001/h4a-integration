<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration;

use Tobb10001\H4aIntegration\Models\Team;
use Tobb10001\H4aIntegration\Persistence\SqliteAdapter;

use PHPUnit\Framework\TestCase;
use SQLite3;
use Tobb10001\H4aIntegration\Util\HttpClient;

/**
 * @covers Tobb10001\H4aIntegration\Updater::replaceLeagueData
 */
class RunUpdateTest extends TestCase
{
    public function testReplaceLeagueData(): void
    {
        // arrange
        $sqlite = new SQLite3(":memory:");
        $adapter = new SqliteAdapter($sqlite);

        $adapter->createTables();

        // create a mock http client
        $mockHttpClient = $this->createMock(HttpClient::class);
        // there will be one request for one team
        $json = json_decode(
            file_get_contents(__DIR__ . "/../assets/league_response.json"),
            true
        );
        $mockHttpClient->expects($this->once())
                       ->method("getJson")
                       ->with("leagueUrl")
                       ->willReturn($json);

        // place a team in the database
        $adapter->insertTeam(new Team([
            "internalName" => "internalName",
            "identificators" => "identificators",
            "leagueUrl" => "leagueUrl",
            "cupUrl" => "cupUrl",
        ]));

        $updater = new Updater($adapter, $mockHttpClient);

        // act: run the update
        $updater->update();

        // assert
        $metadata = $sqlite->query("SELECT COUNT(*) FROM leaguemetadata");
        $games = $sqlite->query("SELECT COUNT(*) FROM games");
        $tabScores = $sqlite->query("SELECT COUNT(*) FROM tabscores");

        $this->assertEquals(1, $metadata->fetchArray()[0]);
        $this->assertEquals(12, $tabScores->fetchArray()[0]);
        $this->assertEquals(22, $games->fetchArray()[0]);
    }
}
