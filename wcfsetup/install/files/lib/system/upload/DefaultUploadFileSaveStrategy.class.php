<?php
namespace wcf\system\upload;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IFile;
use wcf\data\IThumbnailFile;
use wcf\system\event\EventHandler;
use wcf\system\image\ImageHandler;
use wcf\system\WCF;
use wcf\util\ExifUtil;
use wcf\util\FileUtil;

/**
 * Default implementation for saving uploaded files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.upload
 * @category	Community Framework
 * @since	2.2
 */
class DefaultUploadFileSaveStrategy implements IUploadFileSaveStrategy {
	/**
	 * name of the database object action class name
	 * @var	string
	 */
	public $actionClassName = '';
	
	/**
	 * name of the database object editor class name
	 * @var	string
	 */
	public $editorClassName = '';
	
	/**
	 * additional data stored with the default file data
	 * @var	array
	 */
	public $data = [];
	
	/**
	 * created objects
	 * @var	IFile[]
	 */
	public $objects = [];
	
	/**
	 * options handing saving details
	 * 
	 * - bool rotateImages: if true, images are automatically rotated
	 * - bool generateThumbnails: if true, thumbnails are automatically generated after saving file
	 * 
	 * @var	array
	 */
	public $options = [];
	
	/**
	 * Creates a new instance of DefaultUploadFileSaveStrategy.
	 * 
	 * @param	string		$actionClassName
	 * @param	array		$options
	 * @param	array		$data
	 */
	public function __construct($actionClassName, array $options = [ ], array $data = [ ]) {
		$this->actionClassName = $actionClassName;
		$this->options = $options;
		$this->data = $data;
		
		if (!is_subclass_of($this->actionClassName, AbstractDatabaseObjectAction::class)) {
			throw new SystemException("'".$this->actionClassName."' does not extend '".AbstractDatabaseObjectAction::class."'");
		}
		
		$this->editorClassName = (new $this->actionClassName([ ], ''))->getClassName();
		$baseClass = call_user_func([ $this->editorClassName, 'getBaseClass' ]);
		if (!is_subclass_of($baseClass, IFile::class)) {
			throw new SystemException("'".$this->editorClassName."' does not implement '".IFile::class."'");
		}
		if (is_subclass_of($baseClass, IThumbnailFile::class)) {
			$this->options['thumbnailSizes'] = call_user_func([ $baseClass, 'getThumbnailSizes' ]);
		}
	}
	
	/**
	 * Returns the successfully created file objects.
	 * 
	 * @return	IFile[]
	 */
	public function getObjects() {
		return $this->objects;
	}
	
	/**
	 * @inheritdoc
	 */
	public function save(UploadFile $uploadFile) {
		$data = array_merge([
			'filename' => $uploadFile->getFilename(),
			'filesize' => $uploadFile->getFilesize(),
			'fileType' => $uploadFile->getMimeType(),
			'fileHash' => sha1_file($uploadFile->getLocation()),
			'uploadTime' => TIME_NOW,
			'userID' => (WCF::getUser()->userID ?: null)
		], $this->data);
		
		// get image data
		if (($imageData = $uploadFile->getImageData()) !== null) {
			$data['width'] = $imageData['width'];
			$data['height'] = $imageData['height'];
			$data['fileType'] = $imageData['mimeType'];
			
			if (preg_match('~^image/(gif|jpe?g|png)$~i', $data['fileType'])) {
				$data['isImage'] = 1;
			}
		}
		
		$action = new $this->actionClassName([ ], 'create', [
			'data' => $data
		]);
		$object = $action->executeAction()['returnValues'];
		
		$dir = dirname($object->getLocation());
		if (!@file_exists($dir)) {
			FileUtil::makePath($dir, 0777);
		}
		
		// move uploaded file
		if (@move_uploaded_file($uploadFile->getLocation(), $object->getLocation())) {
			// rotate image based on the exif data
			if (!empty($this->options['rotateImages'])) {
				if ($object->isImage) {
					if (FileUtil::checkMemoryLimit($object->width * $object->height * ($object->fileType == 'image/png' ? 4 : 3) * 2.1)) {
						$exifData = ExifUtil::getExifData($object->getLocation());
						if (!empty($exifData)) {
							$orientation = ExifUtil::getOrientation($exifData);
							if ($orientation != ExifUtil::ORIENTATION_ORIGINAL) {
								$adapter = ImageHandler::getInstance()->getAdapter();
								$adapter->loadFile($object->getLocation());
								
								$newImage = null;
								switch ($orientation) {
									case ExifUtil::ORIENTATION_180_ROTATE:
										$newImage = $adapter->rotate(180);
									break;
									
									case ExifUtil::ORIENTATION_90_ROTATE:
										$newImage = $adapter->rotate(90);
									break;
									
									case ExifUtil::ORIENTATION_270_ROTATE:
										$newImage = $adapter->rotate(270);
									break;
									
									case ExifUtil::ORIENTATION_HORIZONTAL_FLIP:
									case ExifUtil::ORIENTATION_VERTICAL_FLIP:
									case ExifUtil::ORIENTATION_VERTICAL_FLIP_270_ROTATE:
									case ExifUtil::ORIENTATION_HORIZONTAL_FLIP_270_ROTATE:
										// unsupported
									break;
								}
								
								if ($newImage !== null) {
									$adapter->load($newImage, $adapter->getType());
								}
								
								$adapter->writeImage($object->getLocation());
								
								// update width, height and filesize of the object
								if ($newImage !== null && ($orientation == ExifUtil::ORIENTATION_90_ROTATE || $orientation == ExifUtil::ORIENTATION_270_ROTATE)) {
									(new $this->editorClassName($object))->update([
										'height' => $object->width,
										'width' => $object->height,
										'filesize' => filesize($object->getLocation())
									]);
								}
							}
						}
					}
				}
			}
			
			$this->objects[$uploadFile->getInternalFileID()] = $object;
		}
		else {
			(new $this->editorClassName($object))->delete();
		}
		
		if ($object->isImage && !empty($this->options['generateThumbnails']) && $object instanceof IThumbnailFile) {
			$this->generateThumbnails($object);
		}
	}
	
	/**
	 * Generates thumbnails for the given file.
	 * 
	 * @param	IThumbnailFile	$file
	 */
	public function generateThumbnails(IThumbnailFile $file) {
		$smallestThumbnailSize = reset($this->options['thumbnailSizes']);
		
		// image is smaller than smallest thumbnail size
		if ($file->width <= $smallestThumbnailSize['width'] && $file->height <= $smallestThumbnailSize['height']) {
			return;
		}
		
		// check memory limit
		if (!FileUtil::checkMemoryLimit($file->width * $file->height * ($file->fileType == 'image/png' ? 4 : 3) * 2.1)) {
			return;
		}
		
		$adapter = ImageHandler::getInstance()->getAdapter();
		$adapter->loadFile($file->getLocation());
		
		$updateData = [ ];
		foreach ($this->options['thumbnailSizes'] as $type => $sizeData) {
			$prefix = 'thumbnail';
			if (!empty($type)) {
				$prefix = $type.'Thumbnail';
			}
			
			$thumbnailLocation = $file->getThumbnailLocation($type);
			
			// delete old thumbnails
			if ($file->{$prefix.'Type'}) {
				@unlink($thumbnailLocation);
				$updateData[$prefix.'Type'] = '';
				$updateData[$prefix.'Size'] = 0;
				$updateData[$prefix.'Width'] = 0;
				$updateData[$prefix.'Height'] = 0;
			}
			
			if ($file->width > $sizeData['width'] || $file->height > $sizeData['height']) {
				$thumbnail = $adapter->createThumbnail($sizeData['width'], $sizeData['height'], isset($sizeData['retainDimensions']) ? $sizeData['retainDimensions'] : true);
				$adapter->writeImage($thumbnail, $thumbnailLocation);
				if (file_exists($thumbnailLocation) && ($imageData = @getimagesize($thumbnailLocation)) !== false) {
					$updateData[$prefix.'Type'] = $imageData['mime'];
					$updateData[$prefix.'Size'] = @filesize($thumbnailLocation);
					$updateData[$prefix.'Width'] = $imageData[0];
					$updateData[$prefix.'Height'] = $imageData[1];
				}
			}
		}
		
		if (!empty($updateData)) {
			(new $this->editorClassName($file))->update($updateData);
		}
	}
}
