<?php
namespace wcf\system\importer;
use wcf\data\user\follow\UserFollow;
use wcf\system\WCF;

/**
 * Imports followers.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class UserFollowerImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = UserFollow::class;
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		$data['followUserID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['followUserID']);
		if (!$data['userID'] || !$data['followUserID']) return 0;
		
		if (!isset($data['time'])) $data['time'] = 0;
		
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_follow
						(userID, followUserID, time)
			VALUES			(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$data['userID'],
			$data['followUserID'],
			$data['time']
		]);
		
		return WCF::getDB()->getInsertID('wcf'.WCF_N.'_user_follow', 'followID');
	}
}
