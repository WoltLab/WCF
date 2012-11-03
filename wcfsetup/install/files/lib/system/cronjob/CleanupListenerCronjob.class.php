<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\cleanup\CleanupHandler;

/**
 * Executes cleanup adapters.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class CleanupListenerCronjob implements ICronjob {
	/**
	 * @see	wcf\system\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		CleanupHandler::getInstance()->execute();
	}
}
