<?php

namespace Tobb10001\H4aIntegration;

use PHPUnit\Framework\TestCase;
use Tobb10001\H4aIntegration\Exceptions\UnsuccessfulRequestException;
use Tobb10001\H4aIntegration\Models\LeagueData;
use Tobb10001\H4aIntegration\Models\Team;
use Tobb10001\H4aIntegration\Persistence\PersistenceInterface;
use Tobb10001\H4aIntegration\Util\HttpClient;

/**
 * @covers Updater;
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
                    "leagueUrl" => "someUrl"
                ])
            ]
        );
        $mockHttp = $this->createMock(HttpClient::class);

        $mockHttp->expects($this->once())
                 ->method("getJson")
                 ->with("someUrl")
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

    public function testUpdateIssuesNoticeOnUnsuccessfulRequest(): void
    {
        $mockPersistence = $this->getMockPersistence(
            [
                new Team([
                    "id" => 1,
                    "internalName" => "HSG Eckbachtal 2",
                    "leagueUrl" => "someUrl"
                ])
            ]
        );
        $mockHttp = $this->createMock(HttpClient::class);

        $mockHttp->expects($this->once())
                 ->method("getJson")
                 ->with("someUrl")
                 ->will($this->throwException(new UnsuccessfulRequestException("")));

        $updater = new Updater($mockPersistence, $mockHttp);

        $this->expectNotice();

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
        $mockHttp = $this->createMock(HttpClient::class);
        $mockHttp->expects($this->never())->method("getJson");

        $updater = new Updater($mockPersistence, $mockHttp);

        $updater->update();
    }
}