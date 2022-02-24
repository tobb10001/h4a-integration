<?php declare(strict_types=1);

namespace Tobb10001\H4aIntegrator\Persistance;

use Tobb10001\H4aIntegration\Models\Team;

interface TeamManagerInterface {

	/**
	 * View all teams, that are registered at the moment.
	 * @return array<Team>
	 */
	public function getTeams(): array;

	/**
	 * Save the given team.
	 * @param Team $team The team to save.
	 * @return bool True, if the team was saved successfully, false otherwise.
	 */
	public function createTeam(Team $team): bool;

	/**
	 * Read a single team, identified by it's ID.
	 * @param int $id The ID of the team, that should be modified.
	 * @return ?Team The requested Team, null, if the Team wasn't found.
	 */
	public function readTeam(int $id): ?Team;

	/**
	 * Modify a team. Search the given team by it's ID and update all fields.
	 * @param Team $team The Team, that replaces the other one.
	 * @return bool True, if the update was successful, false otherwise.
	 */
	public function updateTeam(Team $team): bool;

	/**
	 * Delete a team.
	 * @param int $id The ID of the team to delete.
	 * @return bool True, if the team could be deleted, false otherwise.
	 */
	public function deleteTeam(int $id): bool;

}
