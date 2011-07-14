<?php
namespace wcf\system\cronjob;
use wcf\acp\package\update\PackageUpdate;

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
class GetUpdateInfoCronjob implements Cronjob {
	/**
	 * @see	Cronjob::execute()
	 * @TODO Change path and move method to lib/system/package
	 */
	public function execute(array $data) {
		PackageUpdate::refreshPackageDatabaseAutomatically();
	}
}
