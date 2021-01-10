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
			
			@unlink($this->getLocation($size, false));
		}
		@unlink($this->getLocation('resize', false));
		
		// delete original size
		@unlink($this->getLocation(null, false));
		
		if ($this->hasWebP) {
			@unlink($this->getLocation(null, true));
		}
	}
	
	/**
	 * Creates a WebP variant of the avatar, unless it is a GIF image. If the
	 * user uploads a WebP image, this method will create a JPEG variant as a
	 * fallback for ancient clients.
	 * 
	 * Will return `true` if a variant has been created.
	 *
	 * @since 5.4
	 */
	public function createAvatarVariant(): bool {
		if ($this->hasWebP) {
			return false;
		}
		
		if ($this->avatarExtension === "gif") {
			// We do not touch GIFs at all.
			return false;
		}
		
		// The image adapters do not support the conversion to different
		// file types. However, we do require full GD support for jpeg, png
		// and WebP, therefore we can safely rely on the GD library for
		// the conversion. The images are so small, that neither the speed
		// nor the quality will noticeably differ from Imagick.
		$filename = $this->getLocation();
		$filenameWebP = $this->getLocation(null, true);
		
		$data = ["hasWebP" => 1];
		
		// If the uploaded avatar is already a WebP image, then create a JPEG
		// as a fallback image and flip the image data to match the JPEG.
		if ($this->avatarExtension === "webp") {
			$filenameJpeg = preg_replace('~\.webp$~', '.jpeg', $filenameWebP);
			
			$source = imagecreatefromwebp($filename);
			imagejpeg($source, $filenameJpeg);
			
			$data = [
				"avatarExtension" => "jpeg",
				"fileHash" => sha1_file($filenameJpeg),
			];
		}
		else {
			// The source avatar is not a WebP image.
			switch ($this->avatarExtension) {
				case "jpg":
				case "jpeg":
					$source = imagecreatefromjpeg($filename);
					break;
				
				case "png":
					$source = imagecreatefrompng($filename);
					break;
				
				default:
					throw new \LogicException("Unreachable");
			}
			
			imagewebp($source, $this->getLocation(null, true));
		}
		
		$this->update($data);
		
		return true;
	}
}
