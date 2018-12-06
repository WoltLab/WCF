<?php
namespace wcf\system\user\content\provider;
use wcf\data\like\Like;
use wcf\data\like\LikeList;
use wcf\data\like\object\AbstractLikeObject;
use wcf\data\like\object\LikeObject;
use wcf\data\object\type\IObjectTypeProvider;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\reaction\ReactionHandler;

/**
 * User content provider for reactions.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Content\Provider
 * @since	3.2
 */
class ReactionUserContentProvider extends AbstractDatabaseUserContentProvider {
	/**
	 * @inheritdoc
	 */
	public static function getDatabaseObjectClass() {
		return Like::class;
	}
	
	/**
	 * @inheritdoc
	 */
	public function deleteContent(array $objectIDs) {
		$likeList = new LikeList();
		$likeList->setObjectIDs($objectIDs);
		$likeList->readObjects();
		
		foreach ($likeList as $like) {
			$objectType = ObjectTypeCache::getInstance()->getObjectType($like->objectTypeID);
			
			/** @var IObjectTypeProvider $processor */
			$processor = $objectType->getProcessor();
			
			/** @var AbstractLikeObject $likeableObject */
			$likeableObject = $processor->getObjectByID($like->objectID);
			$likeableObject->setObjectType($objectType);
			
			ReactionHandler::getInstance()->revertReact($like, $likeableObject, LikeObject::getLikeObject($objectType->objectTypeID, $like->objectID), UserRuntimeCache::getInstance()->getObject($like->userID));
		}
	}
}
