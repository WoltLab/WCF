<?php
namespace wcf\system\user\multifactor;
use wcf\system\form\builder\IFormDocument;

/**
 * Handles multi-factor authentication for a specific authentication method.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\System\User\Multifactor
 * @since	5.4
 */
interface IMultifactorMethod {
	/**
	 * Returns a human readable status text regarding the set-up status for the given setup.
	 * 
	 * An example text could be: "5 backup codes remaining".
	 */
	public function getStatusText(int $setupId): string;
	
	/**
	 * Populates the form to set-up and manage this method.
	 */
	public function createManagementForm(IFormDocument $form, ?int $setupId, $returnData = null): void;
	
	/**
	 * Updates the database information based on the data received in the management form.
	 * 
	 * This method will be run within a database transaction and must ensure that a valid database
	 * state is reached. Specifically the multifactor method MUST be usable after this method
	 * finishes successfully.
	 * 
	 * An example of an invalid state could be the removal of all multifactor devices.
	 * 
	 * It is recommended that this method double checks the state of the database to prevent TOCTOU
	 * issues with the validation performed by the form fields and the actual database update.
	 * 
	 * @return	mixed	Opaque data that will be passed as `$returnData` in createManagementForm().
	 */
	public function processManagementForm(IFormDocument $form, int $setupId);
	
	/**
	 * Populates the form to authenticate a user with this method.
	 */
	public function createAuthenticationForm(IFormDocument $form, int $setupId): void;
	
	/**
	 * Updates the database information based on the data received in the authentication form.
	 * 
	 * This method will be run within a database transaction.
	 * 
	 * This method MUST revalidate the information received from the form in a transaction safe way
	 * to prevent concurrent use of the same authentication credentials.
	 * 
	 * An example of such transaction safe use would be invalidating a code by deleting a database row,
	 * checking the number of affected rows and bailing out if the value is not exactly `1`.
	 * 
	 * This method MUST throw an Exception if the database state does not match the expected state.
	 * 
	 * @throws \RuntimeException
	 */
	public function processAuthenticationForm(IFormDocument $form, int $setupId): void;
}
