<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\event\EventHandler;

/**
 * Provides a default implementation for cronjobs. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
abstract class AbstractCronjob implements ICronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		EventHandler::getInstance()->fireAction($this, 'execute');
	}
}
