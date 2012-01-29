<?php
namespace wcf\system\cronjob;
use wcf\data\package\update\PackageUpdate;
use wcf\data\cronjob\Cronjob;

/**
 * Gets update package information.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class GetUpdateInfoCronjob implements ICronjob {
	/**
	 * @see	wcf\system\ICronjob::execute()
	 * @TODO Change path and move method to lib/system/package
	 */
	public function execute(Cronjob $cronjob) {
		//PackageUpdate::refreshPackageDatabaseAutomatically();
	}
}
