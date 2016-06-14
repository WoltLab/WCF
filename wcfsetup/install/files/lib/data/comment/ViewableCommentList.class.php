<?php
namespace wcf\data\comment;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * Represents a list of decorated comment objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Comment
 *
 * @method	ViewableComment		current()
 * @method	ViewableComment[]	getObjects()
 * @method	ViewableComment|null	search($objectID)
 * @property	ViewableComment[]	$objects
 */
class ViewableCommentList extends CommentList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ViewableComment::class;
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();
		
		if (!empty($this->objects)) {
			$userIDs = [];
			foreach ($this->objects as $comment) {
				if ($comment->userID) {
					$userIDs[] = $comment->userID;
				}
			}
			
			if (!empty($userIDs)) {
				UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
			}
		}
	}
}
