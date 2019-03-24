<?php
namespace wcf\data\user\cover\photo;
use wcf\system\style\StyleHandler;

/**
 * Represents a default cover photo.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Cover\Photo
 */
class DefaultUserCoverPhoto implements IUserCoverPhoto {
	/**
	 * @inheritDoc
	 */
	public function delete() {
		/* NOP */
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLocation() {
		return StyleHandler::getInstance()->getStyle()->getCoverPhotoLocation();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getURL() {
		return StyleHandler::getInstance()->getStyle()->getCoverPhotoUrl();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFilename() {
		return StyleHandler::getInstance()->getStyle()->getCoverPhoto();
	}
}
