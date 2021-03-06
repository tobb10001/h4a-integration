<?php

namespace Tobb10001\H4aIntegration;

use PHPUnit\Framework\TestCase;
use Tobb10001\H4aIntegration\Models\LeagueData;
use Tobb10001\H4aIntegration\Models\Team;
use Tobb10001\H4aIntegration\Persistence\PersistenceInterface;
use Tobb10001\H4aIntegration\Util\HttpClientInterface;

/**
 * @covers Tobb10001\H4aIntegration\Updater
 * @uses Tobb10001\H4aIntegration\Util\Json
 * @uses Tobb10001\H4aIntegration\Models\Game
 * @uses Tobb10001\H4aIntegration\Models\GameSchedule
 * @uses Tobb10001\H4aIntegration\Models\LeagueData
 * @uses Tobb10001\H4aIntegration\Models\LeagueMetadata
 * @uses Tobb10001\H4aIntegration\Models\Table
 * @uses Tobb10001\H4aIntegration\Models\TabScore
 * @uses Tobb10001\H4aIntegration\Models\Team
 */
class UpdaterTest extends TestCase
{
    private function getMockPersistence(array $teams): PersistenceInterface
    {
        $mock = $this->createMock(PersistenceInterface::class);
        $mock->expects($this->once())
                        ->method("getTeams")
                        ->willReturn($teams);
        return $mock;
    }

    public function testUpdate(): void
    {
        $mockPersistence = $this->getMockPersistence(
            [
                new Team([
                    "id" => 1,
                    "internalName" => "HSG Eckbachtal 2",
                    "leagueUrl" => "some?lId=1&ogId=1&tId=1"
                ])
            ]
        );
        $mockHttp = $this->createMock(HttpClientInterface::class);

        $mockHttp->expects($this->once())
                 ->method("getJson")
                 ->with("https://spo.handball4all.de/service/if_g_json.php?ca=0&cl=1&cmd=ps&ct=1&og=1")
                 ->willReturn(
                     json_decode(
                         file_get_contents(__DIR__ . "/assets/league_response.json"),
                         true
                     )
                 );

        $mockPersistence->expects($this->once())
                        ->method("replaceLeagueData")
                        ->with(1, $this->isInstanceOf(LeagueData::class))
                        ->willReturn(true);

        $updater = new Updater($mockPersistence, $mockHttp);

        $updater->update();
    }

    public function testUpdateUnupdatableTeams(): void
    {
        $mockPersistence = $this->getMockPersistence(
            [
                new Team([
                    "id" => 1,
                    "internalName" => "HSG Eckbachtal 2",
                ])
            ]
        );
        $mockPersistence->expects($this->never())->method("replaceLeagueData");
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockHttp->expects($this->never())->method("getJson");

        $updater = new Updater($mockPersistence, $mockHttp);

        $updater->update();
    }
}
