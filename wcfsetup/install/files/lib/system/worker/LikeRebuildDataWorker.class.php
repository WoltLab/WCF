<?php
namespace wcf\system\worker;
use wcf\data\like\Like;
use wcf\data\like\LikeList;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Worker implementation for updating likes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 * 
 * @method	LikeList	getObjectList()
 */
class LikeRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = LikeList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 1000;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'like_table.objectID, like_table.likeID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		if (!$this->loopCount) {
			// reset activity points
			UserActivityPointHandler::getInstance()->reset('com.woltlab.wcf.like.activityPointEvent.receivedLikes');
			
			// reset like object data
			$sql = "UPDATE	wcf".WCF_N."_like_object
				SET	likes = 0,
					dislikes = 0,
					cumulativeLikes = 0";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
		}
		
		$itemsToUser = [];
		$likeObjectData = [];
		foreach ($this->objectList as $like) {
			if ($like->objectUserID && $like->likeValue == Like::LIKE) {
				if (!isset($itemsToUser[$like->objectUserID])) {
					$itemsToUser[$like->objectUserID] = 0;
				}
				
				$itemsToUser[$like->objectUserID]++;
			}
			
			if (!isset($likeObjectData[$like->objectTypeID])) {
				$likeObjectData[$like->objectTypeID] = [];
			}
			if (!isset($likeObjectData[$like->objectTypeID][$like->objectID])) {
				$likeObjectData[$like->objectTypeID][$like->objectID] = [
					'likes' => 0,
					'dislikes' => 0,
					'neutralReactions' => 0,
					'cumulativeLikes' => 0,
					'objectUserID' => $like->objectUserID, 
					'cachedReactions' => []
				];
			}
			
			if ($like->isLike()) {
				$likeObjectData[$like->objectTypeID][$like->objectID]['likes']++;
			}
			else if ($like->isDislike()) {
				$likeObjectData[$like->objectTypeID][$like->objectID]['dislikes']++;
			}
			else {
				$likeObjectData[$like->objectTypeID][$like->objectID]['neutralReactions']++;
			}
			$likeObjectData[$like->objectTypeID][$like->objectID]['cumulativeLikes'] += $like->likeValue;
			
			if (!isset($likeObjectData[$like->objectTypeID][$like->objectID]['cachedReactions'][$like->getReactionType()->reactionTypeID])) {
				$likeObjectData[$like->objectTypeID][$like->objectID]['cachedReactions'][$like->getReactionType()->reactionTypeID] = 0;
			}
			
			$likeObjectData[$like->objectTypeID][$like->objectID]['cachedReactions'][$like->getReactionType()->reactionTypeID]++;
		}
		
		// update activity points
		UserActivityPointHandler::getInstance()->fireEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', $itemsToUser, false);
		
		$sql = "INSERT INTO			wcf".WCF_N."_like_object
							(objectTypeID, objectID, objectUserID, likes, dislikes, neutralReactions, cumulativeLikes, cachedReactions)
			VALUES				(?, ?, ?, ?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE		likes = likes + VALUES(likes),
							dislikes = dislikes + VALUES(dislikes),
							neutralReactions = neutralReactions + VALUES(neutralReactions),
							cumulativeLikes = cumulativeLikes + VALUES(cumulativeLikes),
							cachedReactions = VALUES(cachedReactions)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($likeObjectData as $objectTypeID => $objects) {
			foreach ($objects as $objectID => $data) {
				$statement->execute([
					$objectTypeID,
					$objectID,
					$data['objectUserID'],
					$data['likes'],
					$data['dislikes'],
					$data['neutralReactions'],
					$data['cumulativeLikes'], 
					serialize($data['cachedReactions'])
				]);
			}
		}
		WCF::getDB()->commitTransaction();
	}
}
