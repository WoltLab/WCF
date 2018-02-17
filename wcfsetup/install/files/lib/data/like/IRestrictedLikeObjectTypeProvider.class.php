<?php
namespace wcf\data\like;
use wcf\data\like\object\ILikeObject;

/**
 * Extended interface for like object type providers that use different permissions
 * to like content, while using different requirements to display the actual likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Like
 * @since	3.1
 */
interface IRestrictedLikeObjectTypeProvider extends ILikeObjectTypeProvider {
	/**
	 * Returns true if the active user can like the provided object.
	 * 
	 * @param	ILikeObject	$object
	 * @return	boolean
	 */
	public function canLike(ILikeObject $object);
	
	/**
	 * Returns true if the active user can view the likes of to the provided object.
	 * 
	 * @param	ILikeObject	$object
	 * @return	boolean
	 */
	public function canViewLikes(ILikeObject $object);
}
