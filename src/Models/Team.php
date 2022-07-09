<?php

declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

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
        $this->id = array_key_exists('id', $input) ? (int) $input['id'] : null;
        $this->internalName = $input["internalName"];
        $ident = $input["identificators"] ?? null;
        if (is_null($ident)) {
            $this->identificators = [];
        } elseif (is_array($ident)) {
            $this->identificators = $ident;
        } else { /* is_string($identificators) */
            $this->identificators = !empty($ident) ? explode(",", $ident) : [];
        }
        $this->leagueUrl = $input["leagueUrl"] ?? null;
        $this->cupUrl = $input["cupUrl"] ?? null;
    }

    /**
     * Convert the identificators to a string that can be stored in a database.
     * @return string
     */
    public function identificatorStr(): string
    {
        return implode(",", $this->identificators);
    }
}
