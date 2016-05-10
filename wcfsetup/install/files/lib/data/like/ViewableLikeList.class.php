<?php
namespace wcf\data\like;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\like\IViewableLikeProvider;

/**
 * Represents a list of viewable likes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like
 * @category	Community Framework
 *
 * @method	ViewableLike		current()
 * @method	ViewableLike[]		getObjects()
 * @method	ViewableLike|null	search($objectID)
 * @property	ViewableLike[]		$objects
 */
class ViewableLikeList extends LikeList {
	/**
	 * @inheritDoc
	 */
	public $className = Like::class;
	
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ViewableLike::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlLimit = 20;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'like_table.time DESC';
	
	/**
	 * @inheritDoc
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
			UserProfileRuntimeCache::getInstance()->cacheObjectIDs(array_unique($userIDs));
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
