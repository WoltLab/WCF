<?php
namespace wcf\data\like;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfileCache;
use wcf\system\like\IViewableLikeProvider;

/**
 * Represents a list of viewable likes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like
 * @category	Community Framework
 */
class ViewableLikeList extends LikeList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\like\Like';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$decoratorClassName
	 */
	public $decoratorClassName = ViewableLike::class;
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlLimit
	 */
	public $sqlLimit = 20;
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'like_table.time DESC';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		parent::readObjects();
		
		$userIDs = array();
		$likeGroups = array();
		foreach ($this->objects as &$like) {
			$userIDs[] = $like->userID;
			
			if (!isset($likeGroups[$like->objectTypeID])) {
				$objectType = ObjectTypeCache::getInstance()->getObjectType($like->objectTypeID);
				$likeGroups[$like->objectTypeID] = array(
					'provider' => $objectType->getProcessor(),
					'objects' => array()
				);
			}
			
			$likeGroups[$like->objectTypeID]['objects'][] = $like;
		}
		
		// set user profiles
		if (!empty($userIDs)) {
			UserProfileCache::getInstance()->cacheUserIDs(array_unique($userIDs));
		}
		
		// parse like
		foreach ($likeGroups as $likeData) {
			if ($likeData['provider'] instanceof IViewableLikeProvider) {
				$likeData['provider']->prepare($likeData['objects']);
			}
		}
		
		// validate permissions
		foreach ($this->objects as $index => $like) {
			if (!$like->isAccessible()) {
				unset($this->objects[$index]);
			}
		}
		$this->indexToObject = array_keys($this->objects);
	}
	
	/**
	 * Returns timestamp of oldest like fetched.
	 * 
	 * @return	integer
	 */
	public function getLastLikeTime() {
		$lastLikeTime = 0;
		foreach ($this->objects as $like) {
			if (!$lastLikeTime) {
				$lastLikeTime = $like->time;
			}
			
			$lastLikeTime = min($lastLikeTime, $like->time);
		}
		
		return $lastLikeTime;
	}
}
