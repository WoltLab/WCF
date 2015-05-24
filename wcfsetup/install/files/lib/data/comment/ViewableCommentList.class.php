<?php
namespace wcf\data\comment;
use wcf\data\user\UserProfileCache;

/**
 * Represents a list of decorated comment objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment
 * @category	Community Framework
 */
class ViewableCommentList extends CommentList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$decoratorClassName
	 */
	public $decoratorClassName = 'wcf\data\comment\ViewableComment';
	
	public function readObjects() {
		parent::readObjects();
		
		if (!empty($this->objects)) {
			$userIDs = array();
			foreach ($this->objects as $comment) {
				if ($comment->userID) {
					$userIDs[] = $comment->userID;
				}
			}
			
			if (!empty($userIDs)) {
				UserProfileCache::getInstance()->cacheUserIDs($userIDs);
			}
		}
	}
}
