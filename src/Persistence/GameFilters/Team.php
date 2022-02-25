<?php declare(strict_types=1);

/**
 * Filter all games, that belong to one of a set of teams. Teams are identified
 * by their IDs.
 */
class Team {

	/** @var array<int> $ids The IDs of the teams, that should be selected. */
	public array $ids;

	/**
	 * @param int $ids
	 */
	function __construct(...$ids) {
		$this->ids = $ids;
	}
}
