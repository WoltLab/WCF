<?php
namespace wcf\system\worker;
use wcf\data\like\Like;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Worker implementation for updating likes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.worker
 * @category	Community Framework
 */
class LikeRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @see	wcf\system\worker\AbstractRebuildDataWorker::$objectListClassName
	 */
	protected $objectListClassName = 'wcf\data\like\LikeList';
	
	/**
	 * @see	wcf\system\worker\AbstractWorker::$limit
	 */
	protected $limit = 1000;
	
	/**
	 * @see	wcf\system\worker\AbstractRebuildDataWorker::initObjectList
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'like_table.objectID, like_table.likeID';
		$this->objectList->getConditionBuilder()->add('like_table.objectUserID IS NOT NULL');
	}
	
	/**
	 * @see	wcf\system\worker\IWorker::execute()
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
		
		$itemsToUser = array();
		$likeObjectData = array();
		foreach ($this->objectList as $like) {
			if (!isset($itemsToUser[$like->objectUserID])) {
				$itemsToUser[$like->objectUserID] = 0;
			}
			
			$itemsToUser[$like->objectUserID]++;
			
			if (!isset($likeObjectData[$like->objectTypeID])) {
				$likeObjectData[$like->objectTypeID] = array();
			}
			if (!isset($likeObjectData[$like->objectTypeID][$like->objectID])) {
				$likeObjectData[$like->objectTypeID][$like->objectID] = array(
					'likes' => 0,
					'dislikes' => 0,
					'cumulativeLikes' => 0,
					'objectUserID' => $like->objectUserID
				);
			}
			
			if ($like->likeValue == Like::LIKE) {
				$likeObjectData[$like->objectTypeID][$like->objectID]['likes']++;
			}
			else {
				$likeObjectData[$like->objectTypeID][$like->objectID]['dislikes']++;
			}
			$likeObjectData[$like->objectTypeID][$like->objectID]['cumulativeLikes'] += $like->likeValue;
		}
		
		// update activity points
		UserActivityPointHandler::getInstance()->fireEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', $itemsToUser, false);
		
		$sql = "INSERT INTO			wcf".WCF_N."_like_object
							(objectTypeID, objectID, objectUserID, likes, dislikes, cumulativeLikes)
			VALUES				(?, ?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE		likes = VALUES(likes),
							dislikes = VALUES(dislikes),
							cumulativeLikes = VALUES(cumulativeLikes)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($likeObjectData as $objectTypeID => $objects) {
			foreach ($objects as $objectID => $data) {
				$statement->execute(array(
					$objectTypeID,
					$objectID,
					$data['objectUserID'],
					$data['likes'],
					$data['dislikes'],
					$data['cumulativeLikes']
				));
			}
		}
		WCF::getDB()->commitTransaction();
	}
}
