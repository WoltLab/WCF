<?php
namespace wcf\system\worker;
use wcf\data\like\Like;
use wcf\data\user\avatar\UserAvatar;
use wcf\data\user\avatar\UserAvatarEditor;
use wcf\data\user\avatar\UserAvatarList;
use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\data\user\UserProfileAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\image\ImageHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Worker implementation for updating users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 */
class UserRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = UserList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 50;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'user_table.userID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		$users = $userIDs = [];
		foreach ($this->getObjectList() as $user) {
			$users[] = new UserEditor($user);
			$userIDs[] = $user->userID;
		}
		
		// update user ranks
		if (!empty($users)) {
			$action = new UserProfileAction($users, 'updateUserOnlineMarking');
			$action->executeAction();
		}
		
		if (!empty($userIDs)) {
			// update activity points
			UserActivityPointHandler::getInstance()->updateUsers($userIDs);
			
			// update like counter
			if (MODULE_LIKE) {
				$conditionBuilder = new PreparedStatementConditionBuilder();
				$conditionBuilder->add('user_table.userID IN (?)', [$userIDs]);
				$sql = "UPDATE	wcf".WCF_N."_user user_table
					SET	likesReceived = (
							SELECT	COUNT(*)
							FROM	wcf".WCF_N."_like
							WHERE	objectUserID = user_table.userID
								AND likeValue = ".Like::LIKE."
						)
					".$conditionBuilder;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditionBuilder->getParameters());
			}
			
			// update old avatars
			$avatarList = new UserAvatarList();
			$avatarList->getConditionBuilder()->add('user_avatar.userID IN (?)', [$userIDs]);
			$avatarList->getConditionBuilder()->add('(user_avatar.width <> ? OR user_avatar.height <> ?)', [UserAvatar::AVATAR_SIZE, UserAvatar::AVATAR_SIZE]);
			$avatarList->readObjects();
			foreach ($avatarList as $avatar) {
				$width = $avatar->width;
				$height = $avatar->height;
				if ($width != $height) {
					$width = $height = min($width, $height, UserAvatar::AVATAR_SIZE);
					$adapter = ImageHandler::getInstance()->getAdapter();
					$adapter->loadFile($avatar->getLocation());
					$thumbnail = $adapter->createThumbnail($width, $height, false);
					$adapter->writeImage($thumbnail, $avatar->getLocation());
				}
				
				if ($width < UserAvatar::AVATAR_SIZE || $height < UserAvatar::AVATAR_SIZE) {
					$adapter = ImageHandler::getInstance()->getAdapter();
					$adapter->loadFile($avatar->getLocation());
					$adapter->resize(0, 0, $width, $height, UserAvatar::AVATAR_SIZE, UserAvatar::AVATAR_SIZE);
					$adapter->writeImage($adapter->getImage(), $avatar->getLocation());
					$width = $height = UserAvatar::AVATAR_SIZE;
				}
				
				$editor = new UserAvatarEditor($avatar);
				$editor->update([
					'width' => $width,
					'height' => $height
				]);
			}
		}
	}
}
