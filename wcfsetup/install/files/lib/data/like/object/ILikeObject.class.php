<?php
namespace wcf\data\like\object;
use wcf\data\IIDObject;
use wcf\data\like\Like;
use wcf\data\object\type\ObjectType;
use wcf\data\IDatabaseObjectProcessor;
use wcf\data\ITitledObject;

/**
 * Any likeable object should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Like\Object
 */
interface ILikeObject extends IDatabaseObjectProcessor, IIDObject, ITitledObject {
	/**
	 * Returns the url to this likeable.
	 * 
	 * @return	string
	 */
	public function getURL();
	
	/**
	 * Returns the user id of the owner of this object.
	 * 
	 * @return	integer
	 */
	public function getUserID();
	
	/**
	 * Returns the likeable object type previously set via `setObjectType()`.
	 * 
	 * @return	ObjectType
	 */
	public function getObjectType();
	
	/**
	 * Updates the cumulative likes for this object.
	 * 
	 * @param	integer		$cumulativeLikes
	 */
	public function updateLikeCounter($cumulativeLikes);
	
	/**
	 * Sets the likable object type.
	 * 
	 * @param	ObjectType	$objectType
	 */
	public function setObjectType(ObjectType $objectType);
	
	/**
	 * Sends a notification for this like.
	 * 
	 * @param	Like	$like
	 */
	public function sendNotification(Like $like);
	
	/**
	 * Returns the language id of this object or its parent or `null` if no explicit language is set.
	 * 
	 * @return	integer|null
	 */
	public function getLanguageID();
}
