<?php
namespace wcf\data\user\avatar;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit avatars.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Avatar
 * 
 * @method static	UserAvatar	create(array $parameters = [])
 * @method		UserAvatar	getDecoratedObject()
 * @mixin		UserAvatar
 */
class UserAvatarEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserAvatar::class;
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_user_avatar
			WHERE		avatarID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->avatarID]);
		
		$this->deleteFiles();
	}
	
	/**
	 * @inheritDoc
	 */
	public static function deleteAll(array $objectIDs = []) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_avatar
			WHERE	avatarID IN (".str_repeat('?,', count($objectIDs) - 1)."?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($objectIDs);
		while ($avatar = $statement->fetchObject(self::$baseClass)) {
			$editor = new UserAvatarEditor($avatar);
			$editor->deleteFiles();
		}
		
		return parent::deleteAll($objectIDs);
	}
	
	/**
	 * Deletes avatar files.
	 */
	public function deleteFiles() {
		// delete wcf2.1 files
		foreach (UserAvatar::$avatarThumbnailSizes as $size) {
			if ($this->width < $size && $this->height < $size) break;
			
			@unlink($this->getLocation($size));
		}
		@unlink($this->getLocation('resize'));
		
		// delete original size
		@unlink($this->getLocation());
	}
}
