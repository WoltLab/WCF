<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\package\PackageUpdateDispatcher;

/**
 * Gets update package information.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class GetUpdateInfoCronjob implements ICronjob {
	/**
	 * @see	\wcf\system\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		PackageUpdateDispatcher::getInstance()->refreshPackageDatabase();
	}
}
