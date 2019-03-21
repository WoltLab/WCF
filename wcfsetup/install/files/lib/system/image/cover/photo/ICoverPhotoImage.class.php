<?php
namespace wcf\system\image\cover\photo;

/**
 * Default interface for all objects that represent cover photos.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Image\Cover\Photo
 * @since       5.2
 */
interface ICoverPhotoImage {
	/**
	 * Returns the optional caption that should be displayed below the photo.
	 * 
	 * @return string
	 */
	public function getCoverPhotoCaption();
	
	/**
	 * Returns the absolute path to the physical location of the image.
	 * 
	 * @return string
	 */
	public function getCoverPhotoLocation();
	
	/**
	 * Returns the full url of the image.
	 * 
	 * @return string
	 */
	public function getCoverPhotoUrl();
}
