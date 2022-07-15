<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

use Tobb10001\H4aIntegration\Exceptions\InvalidUrlException;
use Tobb10001\H4aIntegration\Exceptions\ProgrammingError;

/**
 * Represents a Team.
 * An operator will store those teams to register them. Once they're registered
 * they will be used to query H4A and download needed data.
 * When querying for games or tables, those Teams will be used as a filter.
 */
class Team
{
    /**
     * @var int $id
     * Non-semantic team identifier.
     */
    public ?int $id;

    /**
     * @var string $internalName
     * A string for the operator to identify this team by.
     * This will also be used to display the team if requested.
     */
    public string $internalName;

    /**
     * @var array<string> $identificators
     * Set of strings to search for in queried data to identify which content
     * (i.e. games, table rows) does belong to the team and which doesn't.
     */
    public array $identificators;

    /**
     * @var ?string $leagurUrl
     * The URL a human would insert into a browser to see league data from the
     * team.
     * This is used to construct the URL, that leads to the actual JSON
     * content and therefore needs to be checked for correctnes. It can also
     * be sent to users to lead them to H4A.
     */
    public ?string $leagueUrl;

    /**
     * @var ?string $cupUrl
     * The URL a human would insert into a browser to see cup data from the
     * team.
     * This is used to construct the URL, that leads to the actual JSON
     * content and therefore needs to be checked for correctnes. It can also
     * be sent to users to lead them to H4A.
     */
    public ?string $cupUrl;

    /**
     * @param array<mixed> $input
     */
    public function __construct(array $input)
    {
        if (!array_key_exists('id', $input) || empty($input['id'])) {
            $this->id = null;
        } else {
            $this->id = (int) $input['id'];
        }

        $this->internalName = $input["internalName"];
        $ident = $input["identificators"] ?? null;
        if (is_null($ident)) {
            $this->identificators = [];
        } elseif (is_array($ident)) {
            $this->identificators = $ident;
        } else { /* is_string($identificators) */
            $this->identificators = !empty($ident) ? explode(",", $ident) : [];
        }

        if (isset($input['leagueUrl'])) {
            if (!$this->checkLeagueUrl($input['leagueUrl'])) {
                throw new InvalidUrlException('The league URL is missing one of it\'s required parameters.');
            }
            $this->leagueUrl = $input["leagueUrl"] ?? null;
        } else {
            $this->leagueUrl = null;
        }

        if (isset($input['cupUrl'])) {
            if (!$this->checkCupUrl($input['cupUrl'])) {
                throw new InvalidUrlException('The cup URL is missing one of it\'s required parameters.');
            }
            $this->cupUrl = $input["cupUrl"] ?? null;
        } else {
            $this->cupUrl = null;
        }
    }

    /**
     * Convert the identificators to a string that can be stored in a database.
     * @return string
     */
    public function identificatorStr(): string
    {
        return implode(",", $this->identificators);
    }

    public function apiUrlLeague(): string
    {
        if (is_null($this->leagueUrl)) {
            throw new ProgrammingError(__METHOD__ . ' was called, although leagueUrl is null.');
        }
        $params = $this->paramsFromUrl($this->leagueUrl);
        return "https://spo.handball4all.de/service/if_g_json.php"
            . "?ca=0&cl={$params['lId']}&cmd=ps&ct={$params['tId']}&og={$params['ogId']}";
    }

    public function apiUrlCup(): string
    {
        if (is_null($this->cupUrl)) {
            throw new ProgrammingError(__METHOD__ . ' was called, although cupUrl is null.');
        }
        $params = $this->paramsFromUrl($this->cupUrl);
        return "https://spo.handball4all.de/service/if_g_json.php"
            . "?ca=0&cl={$params['lId']}&cmd=ps&og={$params['ogId']}&p={$params['pId']}";
    }

    private function checkLeagueUrl(string $url): bool
    {
        return $this->checkParamsExist($url, ['ogId', 'lId', 'tId']);
    }

    private function checkCupUrl(string $url): bool
    {
        return $this->checkParamsExist($url, ['ogId', 'lId', 'pId']);
    }

    /**
     * @param string $url
     * @param array<string> $needed
     */
    private function checkParamsExist(string $url, array $needed): bool
    {
        $params = $this->paramsFromUrl($url);
        foreach ($needed as $need) {
            if (!array_key_exists($need, $params)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array<string, string>
     */
    private function paramsFromUrl(string $url): array
    {
        // custom version of what should be
        // parse_str(parse_url($url, PHP_URL_QUERY))
        // Since H4A-URLs contain a misplaced #-Sign that breaks parse_url we
        // need a special version

        $query = strstr($url, '?');
        if (!$query) {
            throw new InvalidUrlException('URL does not contain a query.');
        }
        $query = substr($query, 1);
        $res = [];
        parse_str($query, $res);
        return $res;
    }
}
