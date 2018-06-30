<?php
declare(strict_types=1);
namespace wcf\system\worker;
use wcf\data\like\Like;
use wcf\data\user\avatar\UserAvatar;
use wcf\data\user\avatar\UserAvatarEditor;
use wcf\data\user\avatar\UserAvatarList;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\data\user\UserProfileAction;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\image\ImageHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Worker implementation for updating users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 * 
 * @method	UserList	getObjectList()
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
		
		$this->objectList->sqlSelects = 'user_option_value.userOption' . User::getUserOptionID('aboutMe') . ' AS aboutMe';
		$this->objectList->sqlJoins = "LEFT JOIN wcf".WCF_N."_user_option_value user_option_value ON (user_option_value.userID = user_table.userID)";
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
			
			// update article counter
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('user_table.userID IN (?)', [$userIDs]);
			$sql = "UPDATE  wcf".WCF_N."_user user_table
				SET     articles = (
						SELECT  COUNT(*)
						FROM    wcf".WCF_N."_article
						WHERE   userID = user_table.userID
					)
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			
			// update like counter
			if (MODULE_LIKE) {
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
			
			// update trophy points
			if (MODULE_TROPHY) {
				$sql = "UPDATE	wcf".WCF_N."_user user_table
					SET	trophyPoints = (
							SELECT		COUNT(*)
							FROM		wcf".WCF_N."_user_trophy user_trophy
							LEFT JOIN	wcf".WCF_N."_trophy trophy ON user_trophy.trophyID = trophy.trophyID
							LEFT JOIN	wcf".WCF_N."_category trophy_category ON trophy.categoryID = trophy_category.categoryID
							WHERE		user_trophy.userID = user_table.userID
									AND trophy.isDisabled = 0
									AND trophy_category.isDisabled = 0
						)
					".$conditionBuilder;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditionBuilder->getParameters());
			}
			
			// update signatures and about me
			$sql = "UPDATE  wcf".WCF_N."_user_option_value
				SET     userOption" . User::getUserOptionID('aboutMe') . " = ?
				WHERE   userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			// retrieve permissions
			$userIDs = [];
			foreach ($users as $user) {
				$userIDs[] = $user->userID;
			}
			$userPermissions = $this->getBulkUserPermissions($userIDs, ['user.message.disallowedBBCodes', 'user.signature.disallowedBBCodes']);
			
			$htmlInputProcessor = new HtmlInputProcessor();
			WCF::getDB()->beginTransaction();
			/** @var UserEditor $user */
			foreach ($users as $user) {
				BBCodeHandler::getInstance()->setDisallowedBBCodes(explode(',', $this->getBulkUserPermissionValue($userPermissions, $user->userID, 'user.signature.disallowedBBCodes')));
				
				if (!$user->signatureEnableHtml) {
					$htmlInputProcessor->process($user->signature, 'com.woltlab.wcf.user.signature', $user->userID, true);
					
					$user->update([
						'signature' => $htmlInputProcessor->getHtml(),
						'signatureEnableHtml' => 1
					]);
				}
				else {
					$htmlInputProcessor->reprocess($user->signature, 'com.woltlab.wcf.user.signature', $user->userID);
					$user->update(['signature' => $htmlInputProcessor->getHtml()]);
				}
				
				if ($user->aboutMe) {
					BBCodeHandler::getInstance()->setDisallowedBBCodes(explode(',', $this->getBulkUserPermissionValue($userPermissions, $user->userID, 'user.message.disallowedBBCodes')));
					
					if (!$user->signatureEnableHtml) {
						$htmlInputProcessor->process($user->aboutMe, 'com.woltlab.wcf.user.aboutMe', $user->userID, true);
					}
					else {
						$htmlInputProcessor->reprocess($user->aboutMe, 'com.woltlab.wcf.user.aboutMe', $user->userID);
					}
					
					$html = $htmlInputProcessor->getHtml();
					// MySQL's TEXT type allows for 65,535 bytes, hence we need to count
					// the bytes rather than the actual amount of characters
					if (strlen($html) > 65535) {
						// content does not fit the available space, and any
						// attempts to truncate it will yield awkward results
						$html = '';
					}
					
					$statement->execute([$html, $user->userID]);
				}
			}
			WCF::getDB()->commitTransaction();
			
			// update old/imported avatars
			$avatarList = new UserAvatarList();
			$avatarList->getConditionBuilder()->add('user_avatar.userID IN (?)', [$userIDs]);
			$avatarList->getConditionBuilder()->add('(user_avatar.width <> ? OR user_avatar.height <> ?)', [UserAvatar::AVATAR_SIZE, UserAvatar::AVATAR_SIZE]);
			$avatarList->readObjects();
			foreach ($avatarList as $avatar) {
				$editor = new UserAvatarEditor($avatar);
				if (!file_exists($avatar->getLocation()) || @getimagesize($avatar->getLocation()) === false) {
					// delete avatars that are missing or broken
					$editor->delete();
					continue;
				}
				
				$width = $avatar->width;
				$height = $avatar->height;
				if ($width != $height) {
					// make avatar quadratic
					$width = $height = min($width, $height, UserAvatar::AVATAR_SIZE);
					$adapter = ImageHandler::getInstance()->getAdapter();
					
					try {
						$adapter->loadFile($avatar->getLocation());
					}
					catch (SystemException $e) {
						// broken image
						$editor->delete();
						continue;
					}
					
					$thumbnail = $adapter->createThumbnail($width, $height, false);
					$adapter->writeImage($thumbnail, $avatar->getLocation());
				}
				
				if ($width != UserAvatar::AVATAR_SIZE || $height != UserAvatar::AVATAR_SIZE) {
					// resize avatar
					$adapter = ImageHandler::getInstance()->getAdapter();
					
					try {
						$adapter->loadFile($avatar->getLocation());
					}
					catch (SystemException $e) {
						// broken image
						$editor->delete();
						continue;
					}
					
					$adapter->resize(0, 0, $width, $height, UserAvatar::AVATAR_SIZE, UserAvatar::AVATAR_SIZE);
					$adapter->writeImage($adapter->getImage(), $avatar->getLocation());
					$width = $height = UserAvatar::AVATAR_SIZE;
				}
				
				$editor->update([
					'width' => $width,
					'height' => $height
				]);
			}
		}
	}
}
