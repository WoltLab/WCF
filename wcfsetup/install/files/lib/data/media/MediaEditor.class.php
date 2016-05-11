<?php
namespace wcf\data\media;
use wcf\data\DatabaseObjectEditor;

/**
 * Procides functions to edit media files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.media
 * @category	Community Framework
 * @since	2.2
 * 
 * @method	Media	getDecoratedObject()
 * @mixin	Media
 */
class MediaEditor extends DatabaseObjectEditor {
	/**
	 * @inheritdoc
	 */
	protected static $baseClass = Media::class;

	/**
	 * Deletes the physical files of the media file.
	 */
	public function deleteFiles() {
		@unlink($this->getLocation());
		
		// delete thumbnails
		if ($this->isImage) {
			foreach (Media::getThumbnailSizes() as $size => $data) {
				@unlink($this->getThumbnailLocation($size));
			}
		}
	}
}
