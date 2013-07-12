<?php
namespace wcf\system\worker;
use wcf\system\user\activity\point\UserActivityPointHandler;

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
		
		$this->objectList->sqlOrderBy = 'like_table.likeID';
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
		}
		
		$itemsToUser = array();
		foreach ($this->objectList as $like) {
			if (!isset($itemsToUser[$like->objectUserID])) {
				$itemsToUser[$like->objectUserID] = 0;
			}
			
			$itemsToUser[$like->objectUserID]++;
		}
		
		// update activity points
		UserActivityPointHandler::getInstance()->fireEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', $itemsToUser, false);
	}
}
