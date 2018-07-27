<?php
namespace wcf\data\user\cover\photo;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;

/**
 * Represents a default cover photo.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
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
		return WCF_DIR . 'images/coverPhotos/' . $this->getFilename();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getURL() {
		return WCF::getPath() . 'images/coverPhotos/' . $this->getFilename();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFilename() {
		return StyleHandler::getInstance()->getStyle()->getCoverPhoto();
	}
}
