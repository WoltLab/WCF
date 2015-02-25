<?php
namespace wcf\system\importer;
use wcf\data\user\avatar\UserAvatarAction;
use wcf\data\user\avatar\UserAvatarEditor;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Imports user avatars.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserAvatarImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\user\avatar\UserAvatar';
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		// check file location
		if (!@file_exists($additionalData['fileLocation'])) return 0;
		
		// get image size
		$imageData = @getimagesize($additionalData['fileLocation']);
		if ($imageData === false) return 0;
		$data['width'] = $imageData[0];
		$data['height'] = $imageData[1];
		// check min size
		if ($data['width'] < 48 || $data['height'] < 48) return 0;
		
		// check image type
		if ($imageData[2] != IMAGETYPE_GIF && $imageData[2] != IMAGETYPE_JPEG && $imageData[2] != IMAGETYPE_PNG) return 0;
		
		// get file hash
		if (empty($data['fileHash'])) $data['fileHash'] = sha1_file($additionalData['fileLocation']);
		
		// get user id
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		if (!$data['userID']) return 0;
		
		// save avatar
		$avatar = UserAvatarEditor::create($data);
		
		// check avatar directory
		// and create subdirectory if necessary
		$dir = dirname($avatar->getLocation());
		if (!@file_exists($dir)) {
			FileUtil::makePath($dir, 0777);
		}
		
		// copy file
		try {
			if (!copy($additionalData['fileLocation'], $avatar->getLocation())) {
				throw new SystemException();
			}
			
			// create thumbnails
			$action = new UserAvatarAction(array($avatar), 'generateThumbnails');
			$action->executeAction();
			
			// update owner
			$sql = "UPDATE	wcf".WCF_N."_user
				SET	avatarID = ?
				WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($avatar->avatarID, $data['userID']));
			
			return $avatar->avatarID;
		}
		catch (SystemException $e) {
			// copy failed; delete avatar
			$editor = new UserAvatarEditor($avatar);
			$editor->delete();
		}
		
		return 0;
	}
}
