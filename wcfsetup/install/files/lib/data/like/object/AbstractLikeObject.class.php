<?php
namespace wcf\data\like\object;
use wcf\data\like\Like;
use wcf\data\object\type\ObjectType;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides a default implementation for like objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Like\Object
 *
 * @method	LikeObject	getDecoratedObject()
 * @mixin	LikeObject
 */
abstract class AbstractLikeObject extends DatabaseObjectDecorator implements ILikeObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = LikeObject::class;
	
	/**
	 * object type
	 * @var	ObjectType
	 */
	protected $objectType = null;
	
	/**
	 * @inheritDoc
	 */
	public function updateLikeCounter($cumulativeLikes) {
		// individual implementations can override this method to update like counter
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectType() {
		return $this->objectType;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setObjectType(ObjectType $objectType) {
		$this->objectType = $objectType;
	}
	
	/**
	 * @inheritDoc
	 */
	public function sendNotification(Like $like) {
		// individual implementations can override this method to provide notifications
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLanguageID() {
		return null;
	}
}
