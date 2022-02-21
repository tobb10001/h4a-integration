<?php declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Util;

use Tobb10001\H4aIntegration\Exceptions\InvalidUrlException;
use Tobb10001\H4aIntegration\Exceptions\UnsuccessfulRequestException;

abstract class RestClient {

    /**
     * Takes the URL, that a browswer-visitor would use to display a league with
     * focus on a team on H4A, and converts it to a URL, that the can be used to
     * retrieve the actual JSON data.
     */
    static function convert_league_url(string $league_url): string {

        if (substr_count($league_url, '?') != 1)
            throw new InvalidUrlException("Wrong number of question makrs '?', exactly one expected");

        /* @phpstan-ignore-next-line */
        $query = substr($league_url, strpos($league_url, '?'));
        parse_str($query, $params);

        if (!array_key_exists("lId", $params) || !array_key_exists("tId", $params))
            throw new InvalidUrlException("Parameter 'lId' or 'tId' is missing.");

        return sprintf(
            "https://spo.handball4all.de/service/if_g_json.php?ca=0&cl=%s&cmd=ps&ct=%s",
            $params["lId"], $params["tId"]);
    }

    /**
     * Dispatch a GET-Request using curl.
     * This is basically a wrapper around curl with the most common options.
     * It return s the response body as a string, ignoring everything else.
     *
     * Might get options to modify the curl request and/or error handling in the
     * future.
     */
    static function dispatch_request(string $url): string {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if ($result === false) 
            throw new UnsuccessfulRequestException("curl_exec returned false");

        curl_close($ch);

        // curl_exec would return bool without CURLOPT_RETURNTRANSFER set.
        // @phpstan-ignore-next-line
        return $result;
    }
}
