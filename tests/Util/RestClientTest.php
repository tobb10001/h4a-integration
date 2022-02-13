<?php

use PHPUnit\Framework\TestCase;

use Tobb10001\H4aIntegration\Exceptions\InvalidUrlException;
use Tobb10001\H4aIntegration\Util\RestClient;

class UrlTest extends TestCase {

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
}
