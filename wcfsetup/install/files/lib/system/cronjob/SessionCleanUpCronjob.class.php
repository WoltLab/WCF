<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\session\SessionHandler;

/**
 * Deletes expired sessions.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
class SessionCleanUpCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		SessionHandler::getInstance()->prune();
	}
}
