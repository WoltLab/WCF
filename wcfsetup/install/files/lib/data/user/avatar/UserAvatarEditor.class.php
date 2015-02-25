<?php
namespace wcf\data\user\avatar;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit avatars.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.avatar
 * @category	Community Framework
 */
class UserAvatarEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\avatar\UserAvatar';
	
	/**
	 * @see	\wcf\data\IEditableObject::delete()
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_user_avatar
			WHERE		avatarID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->avatarID));
		
		$this->deleteFiles();
	}
	
	/**
	 * @see	\wcf\data\IEditableObject::deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
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
		foreach (UserAvatar::$avatarThumbnailSizes as $size) {
			if ($this->width < $size && $this->height < $size) break;
			
			@unlink($this->getLocation($size));
		}
		@unlink($this->getLocation('resize'));
		
		// delete original size
		@unlink($this->getLocation());
	}
}
