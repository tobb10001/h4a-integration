<?php declare(strict_types=1);

namespace Tobb10001\H4aIntegration\Persistance;

use Tobb10001\H4aIntegration\Models\Team;

/**
 * Interface for an Administrator to set up a persistent storage.
 */
interface AdministratorInterface {

	/**
	 * Query the respective object to find out if the infrastructure to save
	 * all required information.
	 * E.g. this function could say if the required database tables currently
	 * exist.
	 */
	public function isReady(): bool;

	/**
	 * Create the infrastructure needed to save the required data.
	 * E.g. this function could create the required database tables.
	 */
	public function installInfrastructure(): void;

	/**
	 * Cleanup the infrastructure created to save the data.
	 * E.g. this function could drop the created database tables.
	 */
	public function uninstallInfrasrtucture(): void;

	/**
	 * Export the currently saved teams to a persistence independent
	 * representation.
	 * @return array<Team>
	 */
	public function getTeams(): array;

}
