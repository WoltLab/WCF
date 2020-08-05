<?php
namespace wcf\system\upload;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IDatabaseObjectAction;
use wcf\data\IFile;
use wcf\data\IThumbnailFile;
use wcf\system\event\EventHandler;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\SystemException;
use wcf\system\image\ImageHandler;
use wcf\system\WCF;
use wcf\util\ExifUtil;
use wcf\util\FileUtil;

/**
 * Default implementation for saving uploaded files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Upload
 * @since	3.0
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
	 * @throws	ImplementationException
	 * @throws	ParentClassException
	 */
	public function __construct($actionClassName, array $options = [], array $data = []) {
		$this->actionClassName = $actionClassName;
		$this->options = $options;
		$this->data = $data;
		
		if (!is_subclass_of($this->actionClassName, AbstractDatabaseObjectAction::class)) {
			throw new ParentClassException($this->actionClassName, AbstractDatabaseObjectAction::class);
		}
		
		/** @noinspection PhpUndefinedMethodInspection */
		$this->editorClassName = (new $this->actionClassName([], ''))->getClassName();
		$baseClass = call_user_func([$this->editorClassName, 'getBaseClass']);
		if (!is_subclass_of($baseClass, IFile::class)) {
			throw new ImplementationException($baseClass, IFile::class);
		}
		if (is_subclass_of($baseClass, IThumbnailFile::class)) {
			$this->options['thumbnailSizes'] = call_user_func([$baseClass, 'getThumbnailSizes']);
		}
		
		$this->options['action'] = $this->options['action'] ?? 'create';
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
	 * @inheritDoc
	 */
	public function save(UploadFile $uploadFile) {
		$data = array_merge([
			'filename' => $uploadFile->getFilename(),
			'filesize' => $uploadFile->getFilesize(),
			'fileType' => $uploadFile->getMimeType(),
			'fileHash' => sha1_file($uploadFile->getLocation()),
			'userID' => WCF::getUser()->userID ?: null,
		], $this->data);
		
		if ($this->options['action'] === 'create') {
			$data['uploadTime'] = TIME_NOW;
		}
		
		// get image data
		if (($imageData = $uploadFile->getImageData()) !== null) {
			$data['width'] = $imageData['width'];
			$data['height'] = $imageData['height'];
			$data['fileType'] = $imageData['mimeType'];
			
			if (preg_match('~^image/(gif|jpe?g|png)$~i', $data['fileType'])) {
				$data['isImage'] = 1;
			}
		}
		
		/** @var IDatabaseObjectAction $action */
		$objects = [];
		if (isset($this->options['object'])) {
			$objects = [$this->options['object']];
		}
		$action = new $this->actionClassName($objects, $this->options['action'], [
			'data' => $data,
		]);
		
		/** @var IThumbnailFile $object */
		$object = $action->executeAction()['returnValues'];
		if (isset($this->options['object'])) {
			$className = get_class($this->options['object']);
			$object = new $className($this->options['object']->getObjectID());
		}
		
		$dir = dirname($object->getLocation());
		if (!@file_exists($dir)) {
			FileUtil::makePath($dir);
		}
		
		// move uploaded file
		if (ENABLE_DEBUG_MODE) {
			$successfulUpload = move_uploaded_file($uploadFile->getLocation(), $object->getLocation());
		}
		else {
			$successfulUpload = @move_uploaded_file($uploadFile->getLocation(), $object->getLocation());
		}
		
		if ($successfulUpload) {
			try {
				$parameters = [
					'object' => $object,
					'updateData' => []
				];
				
				EventHandler::getInstance()->fireAction($this, 'save', $parameters);
				
				if (!is_array($parameters['updateData'])) {
					throw new \UnexpectedValueException('$updateData is no longer an array after being manipulated by event listeners.');
				}
				else {
					$updateData = $parameters['updateData'];
				}
				
				if (!is_object($parameters['object']) || get_class($parameters['object']) !== get_class($object)) {
					throw new \UnexpectedValueException('$object is no longer of class ' . get_class($object) . ' after being manipulated by event listeners.');
				}
				else {
					$object = $parameters['object'];
				}
				
				$adapter = ImageHandler::getInstance()->getAdapter();
				// rotate image based on the exif data
				if (!empty($this->options['rotateImages'])) {
					if ($object->isImage) {
						if ($adapter->checkMemoryLimit($object->width, $object->height, $object->fileType)) {
							$exifData = ExifUtil::getExifData($object->getLocation());
							if (!empty($exifData)) {
								$orientation = ExifUtil::getOrientation($exifData);
								if ($orientation != ExifUtil::ORIENTATION_ORIGINAL) {
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
										if ($newImage instanceof \Imagick) {
											$newImage->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
										}
										
										$adapter->load($newImage, $adapter->getType());
									}
									
									$adapter->writeImage($object->getLocation());
									
									// update width, height and filesize of the object
									if ($newImage !== null && ($orientation == ExifUtil::ORIENTATION_90_ROTATE || $orientation == ExifUtil::ORIENTATION_270_ROTATE)) {
										$updateData = array_merge($updateData, [
											'height' => $object->width,
											'width' => $object->height,
											'filesize' => filesize($object->getLocation())
										]);
									}
									else if ($newImage !== null && $orientation == ExifUtil::ORIENTATION_180_ROTATE) {
										$updateData = array_merge($updateData, [
											'filesize' => filesize($object->getLocation())
										]);
									}
								}
							}
						}
					}
				}
				
				if (!empty($updateData)) {
					/** @var DatabaseObjectEditor $editor */
					$editor = new $this->editorClassName($object);
					$editor->update($updateData);
				}
				
				$this->objects[$uploadFile->getInternalFileID()] = $object;
			}
			catch (SystemException $e) {
				if (ENABLE_DEBUG_MODE) {
					throw $e;
				}
				
				/** @var DatabaseObjectEditor $editor */
				$editor = new $this->editorClassName($object);
				$editor->delete();
			}
		}
		else {
			/** @var DatabaseObjectEditor $editor */
			$editor = new $this->editorClassName($object);
			$editor->delete();
		}
		
		if ($object->isImage && !empty($this->options['generateThumbnails']) && $object instanceof IThumbnailFile) {
			try {
				$this->generateThumbnails($object);
			}
			catch (SystemException $e) {
				if (ENABLE_DEBUG_MODE) {
					throw $e;
				}
				
				/** @var DatabaseObjectEditor $editor */
				$editor = new $this->editorClassName($object);
				$editor->delete();
			}
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
		
		$adapter = ImageHandler::getInstance()->getAdapter();
		
		// check memory limit
		if (!$adapter->checkMemoryLimit($file->width, $file->height, $file->fileType)) {
			return;
		}
		
		$adapter->loadFile($file->getLocation());
		
		$updateData = [];
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
		
		$parameters = [
			'file' => $file,
			'updateData' => $updateData
		];
		
		EventHandler::getInstance()->fireAction($this, 'generateThumbnails', $parameters);
		
		if (!is_array($parameters['updateData'])) {
			throw new \UnexpectedValueException('$updateData is no longer an array after being manipulated by event listeners.');
		}
		else {
			$updateData = $parameters['updateData'];
		}
		
		if (!empty($updateData)) {
			/** @noinspection PhpUndefinedMethodInspection */
			(new $this->editorClassName($file))->update($updateData);
		}
	}
}
