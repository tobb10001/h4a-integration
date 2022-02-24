<?php declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Models;

/**
 * Represents an API response as returned by the League-Endpoint.
 */
class LeagueData {

	/** @var LeagueMetadata $metadata The league's metadata. */
	public LeagueMetadata $metadata;

	/** @var Table $table The table. */
	public Table $table;
	/** @var GameSchedule $games The games (past and future). */
	public GameSchedule $games;

	/**
	 * @param array<mixed> $input
	 * @param Table $table
	 * @param GameSchedule $games
	 */
	public function __construct(array $input, Table $table, GameSchedule $games) {
		$this->metadata = new LeagueMetadata($input);

		$this->table = $table;
		$this->games = $games;
	}
}
