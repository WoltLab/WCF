<?php
namespace wcf\data\like\object;
use wcf\data\like\Like;
use wcf\data\object\type\ObjectType;
use wcf\data\IDatabaseObjectProcessor;
use wcf\data\ITitledObject;

/**
 * Any likeable object should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Like\Object
 */
interface ILikeObject extends IDatabaseObjectProcessor, ITitledObject {
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
	 * Returns the id of this object.
	 * 
	 * @return	integer
	 */
	public function getObjectID();
	
	/**
	 * Returns the object type.
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
	 * Sets the object type.
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
	 * Returns the language id of this object or its parent.
	 * 
	 * @return	integer
	 */
	public function getLanguageID();
}
