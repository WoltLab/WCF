<?php
namespace wcf\system\cronjob;
use wcf\system\cleanup\CleanupHandler;

/**
 * Executes cleanup adapters.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class CleanupListenerCronjob implements ICronjob {
	/**
	 * @see wcf\system\ICronjob::execute()
	 */
	public function execute(array $data) {
		CleanupHandler::getInstance()->execute();
	}
}
