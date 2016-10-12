<?php
namespace wcf\system\importer;
use wcf\data\like\Like;
use wcf\system\WCF;

/**
 * Imports likes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class AbstractLikeImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = Like::class;
	
	/**
	 * object type id for likes
	 * @var	integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		if ($data['objectUserID']) $data['objectUserID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['objectUserID']);
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		if (!$data['userID']) return 0;
		if (empty($data['time'])) $data['time'] = 1;
		
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_like
						(objectID, objectTypeID, objectUserID, userID, time, likeValue)
			VALUES			(?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$data['objectID'],
			$this->objectTypeID,
			$data['objectUserID'],
			$data['userID'],
			$data['time'],
			$data['likeValue']
		]);
		
		return 0;
	}
}
