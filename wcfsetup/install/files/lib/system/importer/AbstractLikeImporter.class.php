<?php
namespace wcf\system\importer;
use wcf\data\like\Like;
use wcf\data\reaction\type\ReactionType;
use wcf\system\reaction\ReactionHandler;
use wcf\system\WCF;

/**
 * Imports likes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
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
		
		if (!isset($data['reactionTypeID'])) {
			if ($data['likeValue'] == 1) {
				$data['reactionTypeID'] = ReactionHandler::getInstance()->getFirstReactionTypeID();
			}
		}
		else {
			$data['reactionTypeID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.reactionType', $data['reactionTypeID']);
		}
		
		if ($data['reactionTypeID'] === null) {
			return 0;
		}
		
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_like
						(objectID, objectTypeID, objectUserID, userID, time, likeValue, reactionTypeID)
			VALUES			(?, ?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$data['objectID'],
			$this->objectTypeID,
			$data['objectUserID'],
			$data['userID'],
			$data['time'],
			$data['likeValue'],
			$data['reactionTypeID']
		]);
		
		return 0;
	}
}
