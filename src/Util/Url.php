<?php

namespace Tobb10001\H4aIntegration\Util;

use Tobb10001\H4aIntegration\Exceptions\InvalidUrlException;

abstract class Url {

    /**
     * Takes the URL, that a browswer-visitor would use to display a league with
     * focus on a team on H4A, and converts it to a URL, that the can be used to
     * retrieve the actual JSON data.
     * @param string $league_url
     * @return string
     */
    static function convert_league_url(string $league_url): string {

        if (substr_count($league_url, '?') != 1) {
            throw new InvalidUrlException("Wrong number of question makrs '?', exactly one expected");
        }

        $query = substr($league_url, strpos($league_url, '?'));
        parse_str($query, $params);

        if (!array_key_exists("lId", $params) || !array_key_exists("tId", $params)) {
            throw new InvalidUrlException("Parameter 'lId' or 'tId' is missing.");
        }

        return sprintf(
            "https://spo.handball4all.de/service/if_g_json.php?ca=0&cl=%s&cmd=ps&ct=%s",
            $params["lId"], $params["tId"]);
    }
}
