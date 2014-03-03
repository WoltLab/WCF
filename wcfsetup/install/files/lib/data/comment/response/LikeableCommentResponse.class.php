<?php
namespace wcf\data\comment\response;
use wcf\data\like\object\AbstractLikeObject;
use wcf\data\object\type\ObjectTypeCache;

/**
 * Likeable object implementation for comment reponses.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class LikeableCommentResponse extends AbstractLikeObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\comment\response\CommentResponse';
	
	/**
	 * @see	\wcf\data\like\object\ILikeObject::getObjectType()
	 */
	public function getObjectType() {
		if ($this->objectType === null) {
			$this->objectType = ObjectTypeCache::getInstance()->getObjectType($this->getDecoratedObject()->objectTypeID);
		}
		
		return $this->objectType;
	}
	
	/**
	 * @see	\wcf\data\ITitledObject::getTitle()
	 */
	public function getTitle() {
		return $this->message;
	}
	
	/**
	 * @see	\wcf\data\like\object\ILikeObject::getURL()
	 */
	public function getURL() {
		return $this->getLink();
	}
	
	/**
	 * @see	\wcf\data\like\object\ILikeObject::getUserID()
	 */
	public function getUserID() {
		return $this->userID;
	}
}
