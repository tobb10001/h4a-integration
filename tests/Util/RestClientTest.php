<?php

namespace Tobb10001\H4aIntegration\Util;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

use Tobb10001\H4aIntegration\Exceptions\InvalidUrlException;
use Tobb10001\H4aIntegration\Exceptions\UnsuccessfulRequestException;

class RestClientTest extends TestCase {

    use PHPMock;

    function test_convert_league_url_valid() {
        $api_url = RestClient::convert_league_url(
            "https://www.handball4all.de/home/portal/pfalz/#/league?ogId=171&lId=789012&tId=123456"
        );

        $this->assertEquals(
            "https://spo.handball4all.de/service/if_g_json.php?ca=0&cl=789012&cmd=ps&ct=123456",
            $api_url
        );
    }

    function test_convert_league_url_multiple_question_marks() {
        $this->expectException(InvalidUrlException::class);
        RestClient::convert_league_url("https://www.handball4all.de/home/portal/pfalz/#/league?ogId=171?lId=789012&tId=123456");
    }

    function test_convert_league_url_missing_parameter() {
        $this->expectException(InvalidUrlException::class);
        RestClient::convert_league_url(
            "https://www.handball4all.de/home/portal/pfalz/#/league?ogId=171&lId=789012"
        );

        $this->expectException(InvalidUrlException::class);
        RestClient::convert_league_url(
            "https://www.handball4all.de/home/portal/pfalz/#/league?ogId=171&tId=123456"
        );
    }

    function test_dispath_request() {
        $url = "dummy_url";
        $output = "dummy_output";

        $curl_init = $this->getFunctionMock(__NAMESPACE__, "curl_init");
        $curl_init
            ->expects($this->once())
            ->willReturn(null);

        $curl_setopt = $this->getFunctionMock(__NAMESPACE__, "curl_setopt");
        $curl_setopt
            ->expects($this->exactly(2))
            ->withConsecutive(
                [null, \CURLOPT_URL, $url],
                [null, \CURLOPT_RETURNTRANSFER, true]);

        $curl_exec = $this->getFunctionMock(__NAMESPACE__, "curl_exec");
        $curl_exec
            ->expects($this->once())
            ->with(null)
            ->willReturn($output);

        $curl_close = $this->getFunctionMock(__NAMESPACE__, "curl_close");
        $curl_close
            ->expects($this->once())
            ->with(null);

        $this->assertEquals($output, RestClient::dispatch_request($url));
    }

    function test_dispatch_request_failed_curl_request() {
        $curl_exec = $this->getFunctionMock(__NAMESPACE__, "curl_exec");
        $curl_exec
            ->expects($this->once())
            ->willReturn(false);

        $this->expectException(UnsuccessfulRequestException::class);
        RestClient::dispatch_request("dummy_url"); 
    }
}
