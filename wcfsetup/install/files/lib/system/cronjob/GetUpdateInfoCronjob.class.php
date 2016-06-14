<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\package\PackageUpdateDispatcher;

/**
 * Gets update package information.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
class GetUpdateInfoCronjob implements ICronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		PackageUpdateDispatcher::getInstance()->refreshPackageDatabase();
	}
}
