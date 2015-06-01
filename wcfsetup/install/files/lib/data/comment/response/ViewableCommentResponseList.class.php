<?php
namespace wcf\data\comment\response;
use wcf\data\user\UserProfileCache;

/**
 * Represents a list of decorated comment response objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class ViewableCommentResponseList extends CommentResponseList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$decoratorClassName
	 */
	public $decoratorClassName = 'wcf\data\comment\response\ViewableCommentResponse';
	
	public function readObjects() {
		parent::readObjects();
		
		if (!empty($this->objects)) {
			$userIDs = array();
			foreach ($this->objects as $response) {
				if ($response->userID) {
					$userIDs[] = $response->userID;
				}
			}
			
			if (!empty($userIDs)) {
				UserProfileCache::getInstance()->cacheUserIDs($userIDs);
			}
		}
	}
}
