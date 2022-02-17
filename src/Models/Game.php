<?php declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

use Tobb10001\H4aIntegration\Util\Json;

/**
 * Represents a game. 
 * Oriented at the ["futureGames"]["games"]-Json.
 */
class Game {
	/** @var string $gID H4A's internal game ID. */
	public string $gID;
	/** @var string $sGID ID for the press report, as soon as it's available. */
	public string $sGID;
	public string $gNo;
	/** @var bool $live Whether the game is currently in progress. */
	public bool $live;
	/** @var string $gToken Token to access the live ticker, if available. */
	public string $gToken;
	public string $gAppid;

	/** @var string $gDate The (start) date the game takes place on. DD.MM.YY */
	public string $gDate;
	/** @var string $gWeekDay German two letter representation of the day of week */
	public string $gWeekDay;
	/** @var string $gTime The time of day the game starts. HH:MM */
	public string $gTime;

	/** @var string $gGymnasiumID H4A's public internal gym ID. */
	public string $gGymnasiumID;
	public string $gGymnasiumNo;
	/** @var string $gGymnasiumName The gym's name. */
	public string $gGymnasiumName;
	/** @var string $gGymnasiumPostal The gym's zip code. */
	public string $gGymnasiumPostal;
	/** @var string $gGymnasiumTown The gym's town. */
	public string $gGymnasiumTown;
	/** @var string $gGymnasiumStreet The gym's street and number. */
	public string $gGymnasiumStreet;

	/** @var string $gHomeTeam The home team name. */
	public string $gHomeTeam;
	/** @var string $gGuestTeam The guest team name. */
	public string $gGuestTeam;
	/** @var ?int $gHomeGoals The home team's goals. */
	public ?int $gHomeGoals;
	/** @var ?int $gGuestGoals The guest team's goals. */
	public ?int $gGuestGoals;
	/** @var ?int $gHomeGoals_1 The home team's half time goals. */
	public ?int $gHomeGoals_1;
	/** @var ?int $gGuestGoals_1 The guest team's half time goals. */
	public ?int $gGuestGoals_1;
	/** @var ?int $gHomePoints The home team's popublic ints. */
	public ?int $gHomePoints;
	/** @var ?int $gGuestPoints The guest team's popublic ints. */	
	public ?int $gGuestPoints;

	public string $gComment;
	public string $gGroupsortTxt;
	public string $gReferee;
	public string $robotextstate;

	/**
	 * @param array<mixed> $input
	 */
	function __construct(array $input) {
							
		$this->gID = $input["gID"];
		$this->sGID = $input["sGID"];
		$this->gNo = $input["gNo"];
		$this->live = $input["live"];
		$this->gToken = $input["gToken"];
		$this->gAppid = $input["gAppid"];

		$this->gDate = $input["gDate"];
		$this->gWeekDay = $input["gWeekDay"];
		$this->gTime = $input["gTime"];

		$this->gGymnasiumID = $input["gGymnasiumID"];
		$this->gGymnasiumNo = $input["gGymnasiumNo"];
		$this->gGymnasiumName = $input["gGymnasiumName"];
		$this->gGymnasiumPostal = $input["gGymnasiumPostal"];
		$this->gGymnasiumTown = $input["gGymnasiumTown"];
		$this->gGymnasiumStreet = $input["gGymnasiumStreet"];

		$this->gHomeTeam = $input["gHomeTeam"];
		$this->gGuestTeam = $input["gGuestTeam"];
		$this->gHomeGoals = Json::int_or_null($input["gHomeGoals"]);
		$this->gGuestGoals = Json::int_or_null($input["gGuestGoals"]);
		$this->gHomeGoals_1 = Json::int_or_null($input["gHomeGoals_1"]);
		$this->gGuestGoals_1 = Json::int_or_null($input["gGuestGoals_1"]);
		$this->gHomePoints = Json::int_or_null($input["gHomePoints"]);
		$this->gGuestPoints = Json::int_or_null($input["gGuestPoints"]);

		$this->gComment = $input["gComment"];
		$this->gGroupsortTxt = $input["gGroupsortTxt"];
		$this->gReferee = $input["gReferee"];
		$this->robotextstate = $input["robotextstate"];
	}
}
