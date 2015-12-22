<?php
namespace wcf\data\media;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISearchAction;
use wcf\data\IUploadAction;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\I18nHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\Linkhandler;
use wcf\system\upload\DefaultUploadFileSaveStrategy;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Executes madia file-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.media
 * @category	Community Framework
 * @since	2.2
 */
class MediaAction extends AbstractDatabaseObjectAction implements ISearchAction, IUploadAction {
	/**
	 * condition builder for searched media file type
	 * @var	PreparedStatementConditionBuilder
	 */
	public $fileTypeConditionBuilder = null;
	
	/**
	 * @inheritdoc
	 */
	public function validateUpload() {
		WCF::getSession()->checkPermissions(['admin.content.cms.canManageMedia']);
		
		// TODO
	}
	
	/**
	 * @inheritdoc
	 */
	public function upload() {
		// save files
		$saveStrategy = new DefaultUploadFileSaveStrategy(self::class, [
			'generateThumbnails' => true,
			'rotateImages' => true
		], [
			'username' => WCF::getUser()->username
		]);
		
		$this->parameters['__files']->saveFiles($saveStrategy);
		$mediaFiles = $saveStrategy->getObjects();
		
		$result = [
			'errors' => [],
			'media' => []
		];
		
		if (!empty($mediaFiles)) {
			// get attachment ids
			$mediaIDs = $mediaToFileID = array();
			foreach ($mediaFiles as $internalFileID => $media) {
				$mediaIDs[] = $media->mediaID;
				$mediaToFileID[$media->mediaID] = $internalFileID;
			}
			
			// get attachments from database (check thumbnail status)
			$mediaList = new MediaList();
			$mediaList->setObjectIDs($mediaIDs);
			$mediaList->readObjects();
			
			foreach ($mediaList as $media) {
				$result['media'][$mediaToFileID[$media->mediaID]] = $this->getMediaData($media);
			}
		}
		
		$files = $this->parameters['__files']->getFiles();
		foreach ($files as $file) {
			if ($file->getValidationErrorType()) {
				$result['errors'][$file->getInternalFileID()] = array(
					'filename' => $file->getFilename(),
					'filesize' => $file->getFilesize(),
					'errorType' => $file->getValidationErrorType()
				);
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
	}
	
	/**
	 * Returns the dialog to manage media.
	 * 
	 * @return	string[]
	 */
	public function getManagementDialog() {
		$mediaList = new ViewableMediaList();
		$mediaList->readObjects();
		
		return [
			'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.media')),
			'media' => $this->getI18nMediaData($mediaList),
			'template' => WCF::getTPL()->fetch('mediaManager', 'wcf', [
				'mediaList' => $mediaList
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
					'title' => [],
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
	 * @inheritdoc
	 */
	public function validateUpdate() {
		WCF::getSession()->checkPermissions(['admin.content.cms.canManageMedia']);
		
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		// TODO: check data
	}
	
	/**
	 * @inheritdoc
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
									$$type = $this->parameters[$type][$language->languageID];
								}
							}
							else {
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
	 * @inheritdoc
	 */
	public function validateGetSearchResultList() {
		if (!WCF::getSession()->getPermission('admin.content.cms.canManageMedia') && !WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
			throw new PermissionDeniedException();
		}
		
		$this->readString('searchString', true, 'data');
		$this->readString('fileType', true, 'data');
		
		if (!$this->parameters['data']['searchString'] && !$this->parameters['data']['fileType']) {
			throw new UserInputException('searchString');
		}
		
		$this->fileTypeConditionBuilder = new PreparedStatementConditionBuilder(false);
		switch ($this->parameters['data']['fileType']) {
			case 'other':
				$this->fileTypeConditionBuilder->add('media.fileType NOT LIKE ?', ['image/%']);
				$this->fileTypeConditionBuilder->add('media.fileType <> ?', ['application/pdf']);
				$this->fileTypeConditionBuilder->add('media.fileType NOT LIKE ?', ['text/%']);
			break;
			
			case 'image':
				$this->fileTypeConditionBuilder->add('media.fileType LIKE ?', ['image/%']);
			break;
			
			case 'pdf':
				$this->fileTypeConditionBuilder->add('media.fileType = ?', ['application/pdf']);
			break;
			
			case 'text':
				$this->fileTypeConditionBuilder->add('media.fileType LIKE ?', ['text/%']);
			break;
		}
	}
	
	/**
	 * @inheritdoc
	 */
	public function getSearchResultList() {
		$searchString = '%'.addcslashes($this->parameters['data']['searchString'], '_%').'%';
		
		$sql = "SELECT		media.mediaID
			FROM		wcf".WCF_N."_media media
			LEFT JOIN	wcf".WCF_N."_media_content media_content
			ON		(media_content.mediaID = media.mediaID)
			WHERE		(media_content.title LIKE ?
					OR media_content.caption LIKE ?
					OR media_content.altText LIKE ?
					OR media.filename LIKE ?)";
		if (!empty($this->fileTypeConditionBuilder->__toString())) {
			$sql .= " AND ".$this->fileTypeConditionBuilder;
		}
		$statement = WCF::getDB()->prepareStatement($sql, 0, 10);
		$statement->execute(array_merge([
			$searchString,
			$searchString,
			$searchString,
			$searchString
		], $this->fileTypeConditionBuilder->getParameters()));
		
		$mediaIDs = [];
		while ($mediaID = $statement->fetchColumn()) {
			$mediaIDs[] = $mediaID;
		}
		
		if (empty($mediaIDs)) {
			return [
				'template' => WCF::getLanguage()->getDynamicVariable('wcf.media.search.noResults')
			];
		}
		
		$mediaList = new ViewableMediaList();
		$mediaList->setObjectIDs($mediaIDs);
		$mediaList->readObjects();
		
		return [
			'media' => $this->getI18nMediaData($mediaList),
			'template' => WCF::getTPL()->fetch('mediaListItems', 'wcf', [
				'mediaList' => $mediaList
			])
		];
	}
	
	/**
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	public function delete() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		/** @var MediaEditor $mediaEditor */
		foreach ($this->objects as $mediaEditor) {
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
			foreach ($this->objects as $media) {
				$mediaIDs[] = $media->mediaID;
			}
		}
		
		if (!empty($mediaIDs)) {
			ClipboardHandler::getInstance()->unmark($mediaIDs, ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.media'));
		}
	}
}
