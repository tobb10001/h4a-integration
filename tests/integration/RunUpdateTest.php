<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration;

use Tobb10001\H4aIntegration\Models\Team;
use Tobb10001\H4aIntegration\Persistence\SqliteAdapter;

use PHPUnit\Framework\TestCase;
use SQLite3;
use Tobb10001\H4aIntegration\Util\HttpClientInterface;

/**
 * @covers Tobb10001\H4aIntegration\Persistence\SqliteAdapter::replaceLeagueData
 * @uses Tobb10001\H4aIntegration\Models\Game
 * @uses Tobb10001\H4aIntegration\Models\GameSchedule
 * @uses Tobb10001\H4aIntegration\Models\LeagueData
 * @uses Tobb10001\H4aIntegration\Models\LeagueMetadata
 * @uses Tobb10001\H4aIntegration\Models\TabScore
 * @uses Tobb10001\H4aIntegration\Models\Table
 * @uses Tobb10001\H4aIntegration\Models\Team
 * @uses Tobb10001\H4aIntegration\Persistence\SqliteAdapter
 * @uses Tobb10001\H4aIntegration\Updater
 * @uses Tobb10001\H4aIntegration\Util\Json
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
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        // there will be one request for one team
        $json = json_decode(
            file_get_contents(__DIR__ . "/../assets/league_response.json"),
            true
        );
        $mockHttpClient->expects($this->exactly(2))
                       ->method("getJson")
                       ->withConsecutive(
                           [
                           "https://spo.handball4all.de/service/if_g_json.php?ca=0&cl=1&cmd=ps&ct=1&og=1"],
                           ["https://spo.handball4all.de/service/if_g_json.php?ca=0&cl=1&cmd=ps&og=1&p=1"]
                       )->willReturn($json);

        // place a team in the database
        $adapter->insertTeam(new Team([
            "internalName" => "internalName",
            "identificators" => "identificators",
            "leagueUrl" => "some?lId=1&ogId=1&tId=1",
            "cupUrl" => "some?pId=1&ogId=1&lId=1",
        ]));

        $updater = new Updater($adapter, $mockHttpClient);

        // act: run the update
        $updater->update();

        // assert
        $metadata = $sqlite->query("SELECT COUNT(*) FROM leaguemetadata");
        $games = $sqlite->query("SELECT COUNT(*) FROM games");
        $tabScores = $sqlite->query("SELECT COUNT(*) FROM tabscores");

        $this->assertEquals(2, $metadata->fetchArray()[0]);
        $this->assertEquals(24, $tabScores->fetchArray()[0]);
        $this->assertEquals(44, $games->fetchArray()[0]);
    }
}
