<?php
namespace wcf\data\user\cover\photo;
use wcf\system\WCF;

/**
 * Represents a user's cover photo.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Cover\Photo
 */
class UserCoverPhoto implements IUserCoverPhoto {
	/**
	 * file extension
	 * @var string
	 */
	protected $coverPhotoExtension;
	
	/**
	 * file hash
	 * @var string
	 */
	protected $coverPhotoHash;
	
	/**
	 * user id
	 * @var integer
	 */
	protected $userID;
	
	const MAX_HEIGHT = 400;
	const MAX_WIDTH = 1600;
	const MIN_HEIGHT = 200;
	const MIN_WIDTH = 800;
	
	/**
	 * UserCoverPhoto constructor.
	 * 
	 * @param       integer         $userID
	 * @param       string          $coverPhotoHash
	 * @param       string          $coverPhotoExtension
	 */
	public function __construct($userID, $coverPhotoHash, $coverPhotoExtension) {
		$this->userID = $userID;
		$this->coverPhotoHash = $coverPhotoHash;
		$this->coverPhotoExtension = $coverPhotoExtension;
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
		return substr($this->coverPhotoHash, 0, 2) . '/' . $this->userID . '-' . $this->coverPhotoHash . '.' . $this->coverPhotoExtension;
	}
}
