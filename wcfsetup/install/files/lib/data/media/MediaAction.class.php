<?php
namespace wcf\data\media;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISearchAction;
use wcf\data\IUploadAction;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\upload\DefaultUploadFileSaveStrategy;
use wcf\system\upload\MediaUploadFileValidationStrategy;
use wcf\system\upload\UploadFile;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Executes madia file-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Media
 * @since	3.0
 * 
 * @method	Media		create()
 * @method	MediaEditor[]	getObjects()
 * @method	MediaEditor	getSingleObject()
 */
class MediaAction extends AbstractDatabaseObjectAction implements ISearchAction, IUploadAction {
	/**
	 * @inheritDoc
	 */
	public function validateUpload() {
		WCF::getSession()->checkPermissions(['admin.content.cms.canManageMedia']);
		
		if (isset($this->parameters['fileTypeFilters']) && !is_array($this->parameters['fileTypeFilters'])) {
			throw new UserInputException('fileTypeFilters');
		}
		
		/** @noinspection PhpUndefinedMethodInspection */
		$this->parameters['__files']->validateFiles(new MediaUploadFileValidationStrategy(isset($this->parameters['fileTypeFilters']) ? $this->parameters['fileTypeFilters'] : []));
	}
	
	/**
	 * @inheritDoc
	 */
	public function upload() {
		// save files
		$saveStrategy = new DefaultUploadFileSaveStrategy(self::class, [
			'generateThumbnails' => true,
			'rotateImages' => true
		], [
			'username' => WCF::getUser()->username
		]);
		
		/** @noinspection PhpUndefinedMethodInspection */
		$this->parameters['__files']->saveFiles($saveStrategy);
		
		/** @var Media[] $mediaFiles */
		$mediaFiles = $saveStrategy->getObjects();
		
		$result = [
			'errors' => [],
			'media' => []
		];
		
		if (!empty($mediaFiles)) {
			$mediaIDs = $mediaToFileID = [];
			foreach ($mediaFiles as $internalFileID => $media) {
				$mediaIDs[] = $media->mediaID;
				$mediaToFileID[$media->mediaID] = $internalFileID;
			}
			
			// fetch media objects from database
			$mediaList = new MediaList();
			$mediaList->setObjectIDs($mediaIDs);
			$mediaList->readObjects();
			
			foreach ($mediaList as $media) {
				$result['media'][$mediaToFileID[$media->mediaID]] = $this->getMediaData($media);
			}
		}
		
		/** @var UploadFile[] $files */
		/** @noinspection PhpUndefinedMethodInspection */
		$files = $this->parameters['__files']->getFiles();
		foreach ($files as $file) {
			if ($file->getValidationErrorType()) {
				$result['errors'][$file->getInternalFileID()] = [
					'filename' => $file->getFilename(),
					'filesize' => $file->getFilesize(),
					'errorType' => $file->getValidationErrorType()
				];
			}
		}
		
		return $result;
	}
	
	/**
	 * Returns the data of the media file to be returned by AJAX requests.
	 * 
	 * @param	object		$media		media files whose data will be returned
	 * @return	string[]
	 */
	protected function getMediaData($media) {
		return [
			'altText' => $media instanceof ViewableMedia ? $media->altText : [],
			'caption' => $media instanceof ViewableMedia ? $media->caption : [],
			'fileHash' => $media->fileHash,
			'filename' => $media->filename,
			'filesize' => $media->filesize,
			'formattedFilesize' => FileUtil::formatFilesize($media->filesize),
			'fileType' => $media->fileType,
			'height' => $media->height,
			'languageID' => $media->languageID,
			'isImage' => $media->isImage,
			'isMultilingual' => $media->isMultilingual,
			'largeThumbnailHeight' => $media->largeThumbnailHeight,
			'largeThumbnailLink' => $media->largeThumbnailType ? $media->getThumbnailLink('large') : '',
			'largeThumbnailType' => $media->largeThumbnailType,
			'largeThumbnailWidth' => $media->largeThumbnailWidth,
			'link' => $media->getLink(),
			'mediaID' => $media->mediaID,
			'mediumThumbnailHeight' => $media->mediumThumbnailHeight,
			'mediumThumbnailLink' => $media->mediumThumbnailType ? $media->getThumbnailLink('medium') : '',
			'mediumThumbnailType' => $media->mediumThumbnailType,
			'mediumThumbnailWidth' => $media->mediumThumbnailWidth,
			'smallThumbnailHeight' => $media->smallThumbnailHeight,
			'smallThumbnailLink' => $media->smallThumbnailType ? $media->getThumbnailLink('small') : '',
			'smallThumbnailType' => $media->smallThumbnailType,
			'smallThumbnailWidth' => $media->smallThumbnailWidth,
			'tinyThumbnailHeight' => $media->tinyThumbnailHeight,
			'tinyThumbnailLink' => $media->tinyThumbnailType ? $media->getThumbnailLink('tiny') : '',
			'tinyThumbnailType' => $media->tinyThumbnailType,
			'tinyThumbnailWidth' => $media->tinyThumbnailWidth,
			'title' => $media instanceof ViewableMedia ? $media->title : [],
			'uploadTime' => $media->uploadTime,
			'userID' => $media->userID,
			'userLink' => $media->userID ? LinkHandler::getInstance()->getLink('User', [
				'id' => $media->userID,
				'title' => $media->username
			]) : '',
			'username' => $media->username,
			'width' => $media->width
		];
	}
	
	/**
	 * Validates the 'getManagementDialog' action.
	 */
	public function validateGetManagementDialog() {
		if (!WCF::getSession()->getPermission('admin.content.cms.canManageMedia') && !WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
			throw new PermissionDeniedException();
		}
		
		if (isset($this->parameters['fileTypeFilters']) && !is_array($this->parameters['fileTypeFilters'])) {
			throw new UserInputException('fileTypeFilters');
		}
		
		$this->readString('mode');
		if ($this->parameters['mode'] != 'editor' && $this->parameters['mode'] != 'select') {
			throw new UserInputException('mode');
		}
	}
	
	/**
	 * Returns the dialog to manage media.
	 * 
	 * @return	string[]
	 */
	public function getManagementDialog() {
		$mediaList = new ViewableMediaList();
		if (!empty($this->parameters['fileTypeFilters'])) {
			$mediaList->addFileTypeFilters($this->parameters['fileTypeFilters']);
		}
		$mediaList->readObjects();
		
		return [
			'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.media')),
			'media' => $this->getI18nMediaData($mediaList),
			'template' => WCF::getTPL()->fetch('mediaManager', 'wcf', [
				'mediaList' => $mediaList,
				'mode' => $this->parameters['mode'],
				'showFileTypeFilter' => empty($this->parameters['fileTypeFilters'])
			])
		];
	}
	
	/**
	 * Returns the complete i18n data of the media files in the given list.
	 * 
	 * @param	MediaList	$mediaList
	 * @return	array
	 */
	protected function getI18nMediaData(MediaList $mediaList) {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('mediaID IN (?)', [$mediaList->getObjectIDs()]);
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_media_content
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		$mediaData = [];
		while ($row = $statement->fetchArray()) {
			if (!isset($mediaData[$row['mediaID']])) {
				$mediaData[$row['mediaID']] = [
					'altText' => [],
					'caption' => [],
					'title' => []
				];
			}
			
			$mediaData[$row['mediaID']]['altText'][intval($row['languageID'])] = $row['altText'];
			$mediaData[$row['mediaID']]['caption'][intval($row['languageID'])] = $row['caption'];
			$mediaData[$row['mediaID']]['title'][intval($row['languageID'])] = $row['title'];
		}
		
		$i18nMediaData = [];
		foreach ($mediaList as $media) {
			if (!isset($mediaData[$media->mediaID])) {
				$mediaData[$media->mediaID] = [];
			}
			
			$i18nMediaData[$media->mediaID] = array_merge($this->getMediaData($media), $mediaData[$media->mediaID]);
		}
		
		return $i18nMediaData;
	}
	
	/**
	 * Validates the 'getEditorDialog' action.
	 */
	public function validateGetEditorDialog() {
		WCF::getSession()->checkPermissions(['admin.content.cms.canManageMedia']);
	}
	
	/**
	 * Returns the template for the media editor.
	 * 
	 * @return	string[]
	 */
	public function getEditorDialog() {
		I18nHandler::getInstance()->register('title');
		I18nHandler::getInstance()->register('caption');
		I18nHandler::getInstance()->register('altText');
		I18nHandler::getInstance()->assignVariables();
		
		return [
			'template' => WCF::getTPL()->fetch('mediaEditor', 'wcf', [
				'languageID' => WCF::getUser()->languageID,
				'languages' => LanguageFactory::getInstance()->getLanguages()
			])
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateUpdate() {
		WCF::getSession()->checkPermissions(['admin.content.cms.canManageMedia']);
		
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		$this->readInteger('languageID', true, 'data');
		$this->readBoolean('isMultilingual', true, 'data');
		
		// languageID: convert zero to null
		if (!$this->parameters['data']['languageID']) $this->parameters['data']['languageID'] = null;
		
		// isMultilingual: convert boolean to integer
		$this->parameters['data']['isMultilingual'] = intval($this->parameters['data']['isMultilingual']);
		
		// if data is not multilingual, a language id has to be given
		if (!$this->parameters['data']['isMultilingual'] && !$this->parameters['data']['languageID']) {
			throw new UserInputException('languageID');
		}
		
		// check language id
		if ($this->parameters['data']['languageID'] && !LanguageFactory::getInstance()->getLanguage($this->parameters['data']['languageID'])) {
			throw new UserInputException('languageID');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		parent::update();
		
		if (count($this->objects) == 1 && (isset($this->parameters['title']) || isset($this->parameters['caption']) || isset($this->parameters['altText']))) {
			$media = reset($this->objects);
			
			$isMultilingual = $media->isMultilingual;
			if (isset($this->parameters['data']['isMultilingual'])) {
				$isMultilingual = $this->parameters['data']['isMultilingual'];
			}
			
			$sql = "DELETE FROM	wcf".WCF_N."_media_content
				WHERE		mediaID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$media->mediaID]);
			
			$sql = "INSERT INTO	wcf".WCF_N."_media_content
						(mediaID, languageID, title, caption, altText)
				VALUES		(?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			if (!$isMultilingual) {
				$languageID = $media->languageID;
				if (isset($this->parameters['data']['languageID'])) {
					$languageID = $this->parameters['data']['languageID'];
				}
				$statement->execute([
					$media->mediaID,
					$languageID,
					isset($this->parameters['title'][$languageID]) ? $this->parameters['title'][$languageID] : '',
					isset($this->parameters['caption'][$languageID]) ? $this->parameters['caption'][$languageID] : '',
					isset($this->parameters['altText'][$languageID]) ? $this->parameters['altText'][$languageID] : ''
				]);
			}
			else {
				$languages = LanguageFactory::getInstance()->getLanguages();
				foreach ($languages as $language) {
					$title = $caption = $altText = '';
					foreach (['title', 'caption', 'altText'] as $type) {
						if (isset($this->parameters[$type])) {
							if (is_array($this->parameters[$type])) {
								if (isset($this->parameters[$type][$language->languageID])) {
									/** @noinspection PhpVariableVariableInspection */
									$$type = $this->parameters[$type][$language->languageID];
								}
							}
							else {
								/** @noinspection PhpVariableVariableInspection */
								$$type = $this->parameters[$type];
							}
						}
					}
					
					$statement->execute([
						$media->mediaID,
						$language->languageID,
						$title,
						$caption,
						$altText
					]);
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateGetSearchResultList() {
		if (!WCF::getSession()->getPermission('admin.content.cms.canManageMedia') && !WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
			throw new PermissionDeniedException();
		}
		
		$this->readString('searchString', true);
		$this->readString('fileType', true);
		
		if (!$this->parameters['searchString'] && !$this->parameters['fileType']) {
			throw new UserInputException('searchString');
		}
		
		if (isset($this->parameters['fileTypeFilters']) && !is_array($this->parameters['fileTypeFilters'])) {
			throw new UserInputException('fileTypeFilters');
		}
		
		$this->readString('mode');
		if ($this->parameters['mode'] != 'editor' && $this->parameters['mode'] != 'select') {
			throw new UserInputException('mode');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSearchResultList() {
		$mediaList = new MediaList();
		$mediaList->addSearchConditions($this->parameters['searchString']);
		if (!empty($this->parameters['fileType'])) {
			$mediaList->addDefaultFileTypeFilter($this->parameters['fileType']);
		}
		if (!empty($this->parameters['fileTypeFilters'])) {
			$mediaList->addFileTypeFilters($this->parameters['fileTypeFilters']);
		}
		
		$mediaList->readObjectIDs();
		
		if (empty($mediaList->getObjectIDs())) {
			return [
				'template' => WCF::getLanguage()->getDynamicVariable('wcf.media.search.noResults')
			];
		}
		
		$viewableMediaList = new ViewableMediaList();
		$viewableMediaList->setObjectIDs($mediaList->getObjectIDs());
		$viewableMediaList->readObjects();
		
		return [
			'media' => $this->getI18nMediaData($viewableMediaList),
			'template' => WCF::getTPL()->fetch('mediaListItems', 'wcf', [
				'mediaList' => $viewableMediaList,
				'mode' => $this->parameters['mode']
			])
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateDelete() {
		WCF::getSession()->checkPermissions(['admin.content.cms.canManageMedia']);
		
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->getObjects() as $mediaEditor) {
			$mediaEditor->deleteFiles();
		}
		
		parent::delete();
		
		$this->unmarkItems();
	}
	
	/**
	 * Unmarks the media files with the given ids. If no media ids are given,
	 * all media files currently loaded are unmarked.
	 * 
	 * @param	integer[]	$mediaIDs	ids of the media files to be unmarked
	 */
	protected function unmarkItems(array $mediaIDs = []) {
		if (empty($mediaIDs)) {
			foreach ($this->getObjects() as $media) {
				$mediaIDs[] = $media->mediaID;
			}
		}
		
		if (!empty($mediaIDs)) {
			ClipboardHandler::getInstance()->unmark($mediaIDs, ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.media'));
		}
	}
}
