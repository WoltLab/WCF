<?php
namespace wcf\data\media;
use wcf\data\category\CategoryNodeTree;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISearchAction;
use wcf\data\IUploadAction;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\category\CategoryHandler;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\upload\DefaultUploadFileSaveStrategy;
use wcf\system\upload\MediaReplaceUploadFileValidationStrategy;
use wcf\system\upload\MediaUploadFileValidationStrategy;
use wcf\system\upload\UploadFile;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Executes media file-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
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
	 * number of media files per media manager dialog page
	 */
	const ITEMS_PER_MANAGER_DIALOG_PAGE = 50;
	
	/**
	 * @inheritDoc
	 */
	public function validateUpload() {
		WCF::getSession()->checkPermissions(['admin.content.cms.canManageMedia']);
		
		$this->readBoolean('imagesOnly', true);
		$this->readInteger('categoryID', true);
		
		/** @noinspection PhpUndefinedMethodInspection */
		$this->parameters['__files']->validateFiles(new MediaUploadFileValidationStrategy($this->parameters['imagesOnly']));
		
		if ($this->parameters['categoryID']) {
			$category = CategoryHandler::getInstance()->getCategory($this->parameters['categoryID']);
			if ($category === null || $category->getObjectType()->objectType !== 'com.woltlab.wcf.media.category') {
				throw new UserInputException('categoryID');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function upload() {
		$additionalData = ['username' => WCF::getUser()->username];
		if ($this->parameters['categoryID']) {
			$additionalData['categoryID'] = $this->parameters['categoryID'];
		}
		
		// save files
		$saveStrategy = new DefaultUploadFileSaveStrategy(self::class, [
			'generateThumbnails' => true,
			'rotateImages' => true
		], $additionalData);
		
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
			$mediaList = new ViewableMediaList();
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
	 * Generates thumbnails.
	 */
	public function generateThumbnails() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$saveStrategy = new DefaultUploadFileSaveStrategy(self::class);
		
		foreach ($this->getObjects() as $mediaEditor) {
			if ($mediaEditor->getDecoratedObject()->isImage) {
				$saveStrategy->generateThumbnails($mediaEditor->getDecoratedObject());
			}
		}
	}
	
	/**
	 * Returns the data of the media file to be returned by AJAX requests.
	 * 
	 * @param	Media|ViewableMedia	$media		media files whose data will be returned
	 * @return	string[]
	 */
	protected function getMediaData($media) {
		return [
			'altText' => $media instanceof ViewableMedia ? $media->altText : [],
			'caption' => $media instanceof ViewableMedia ? $media->caption : [],
			'captionEnableHtml' => $media->captionEnableHtml,
			'categoryID' => $media->categoryID,
			'elementTag' => $media instanceof ViewableMedia ? $media->getElementTag($this->parameters['elementTagSize'] ?? 144) : '',
			'elementTag48' => $media instanceof ViewableMedia ? $media->getElementTag(48) : '',
			'fileHash' => $media->fileHash,
			'filename' => $media->filename,
			'filesize' => $media->filesize,
			'formattedFilesize' => FileUtil::formatFilesize($media->filesize),
			'fileType' => $media->fileType,
			'height' => $media->height,
			'languageID' => $media->languageID,
			'imageDimensions' => $media->isImage ? WCF::getLanguage()->getDynamicVariable('wcf.media.imageDimensions.value', [
				'media' => $media,
			]) : '',
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
			'smallThumbnailTag' => $media->smallThumbnailType ? $media->getThumbnailTag('small') : '',
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
			'userLinkElement' => $media instanceof ViewableMedia ? WCF::getTPL()->fetchString(
				WCF::getTPL()->getCompiler()->compileString('userLink', '{user object=$userProfile}')['template'],
				['userProfile' => $media->getUserProfile()]
			) : '',
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
		
		$this->readBoolean('imagesOnly', true);
		
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
		if (WCF::getSession()->getPermission('admin.content.cms.canOnlyAccessOwnMedia')) {
			$mediaList->getConditionBuilder()->add('media.userID = ?', [WCF::getUser()->userID]);
		}
		if ($this->parameters['imagesOnly']) {
			$mediaList->getConditionBuilder()->add('media.isImage = ?', [1]);
		}
		$mediaList->sqlOrderBy = 'media.uploadTime DESC, media.mediaID DESC';
		$mediaList->sqlLimit = static::ITEMS_PER_MANAGER_DIALOG_PAGE;
		$mediaList->readObjects();
		
		$categoryList = (new CategoryNodeTree('com.woltlab.wcf.media.category'))->getIterator();
		$categoryList->setMaxDepth(0);
		
		return [
			'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.media')),
			'media' => $this->getI18nMediaData($mediaList),
			'pageCount' => ceil($mediaList->countObjects() / static::ITEMS_PER_MANAGER_DIALOG_PAGE),
			'template' => WCF::getTPL()->fetch('mediaManager', 'wcf', [
				'categoryList' => $categoryList,
				'mediaList' => $mediaList,
				'mode' => $this->parameters['mode']
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
		if (!count($mediaList)) return [];
		
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
		
		$this->getSingleObject();
		
		if (!$this->getSingleObject()->canManage()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Returns the template for the media editor.
	 * 
	 * @return	string[]
	 */
	public function getEditorDialog() {
		$mediaList = new ViewableMediaList();
		$mediaList->setObjectIDs([$this->getSingleObject()->mediaID]);
		$mediaList->readObjects();
		$media = $mediaList->search($this->getSingleObject()->mediaID);
		
		I18nHandler::getInstance()->register('title_' . $media->mediaID);
		I18nHandler::getInstance()->register('caption_' . $media->mediaID);
		I18nHandler::getInstance()->register('altText_' . $media->mediaID);
		I18nHandler::getInstance()->assignVariables();
		
		$categoryList = (new CategoryNodeTree('com.woltlab.wcf.media.category'))->getIterator();
		$categoryList->setMaxDepth(0);
		
		return [
			'availableLanguageCount' => count(LanguageFactory::getInstance()->getLanguages()),
			'categoryIDs' => array_keys(CategoryHandler::getInstance()->getCategories('com.woltlab.wcf.media.category')),
			'mediaData' => $this->getI18nMediaData($mediaList)[$this->getSingleObject()->mediaID],
			'template' => WCF::getTPL()->fetch('mediaEditor', 'wcf', [
				'__aclSimplePrefix' => 'mediaEditor_' . $media->mediaID . '_',
				'__languageChooserPrefix' => 'mediaEditor_' . $media->mediaID . '_',
				'aclValues' => SimpleAclHandler::getInstance()->getValues('com.woltlab.wcf.media', $media->mediaID),
				'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
				'categoryList' => $categoryList,
				'languageID' => WCF::getUser()->languageID,
				'languages' => LanguageFactory::getInstance()->getLanguages(),
				'media' => $media
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
		
		if (WCF::getSession()->getPermission('admin.content.cms.canOnlyAccessOwnMedia')) {
			foreach ($this->getObjects() as $media) {
				if ($media->userID != WCF::getUser()->userID) {
					throw new PermissionDeniedException();
				}
			}
		}
		
		$this->readInteger('categoryID', true, 'data');
		$this->readInteger('languageID', true, 'data');
		$this->readBoolean('isMultilingual', true, 'data');
		
		if (count(LanguageFactory::getInstance()->getLanguages()) > 1) {
			// languageID: convert zero to null
			if (!$this->parameters['data']['languageID']) $this->parameters['data']['languageID'] = null;
			
			// isMultilingual: convert boolean to integer
			$this->parameters['data']['isMultilingual'] = intval($this->parameters['data']['isMultilingual']);
		}
		else {
			$this->parameters['data']['isMultilingual'] = 0;
			$this->parameters['data']['languageID'] = WCF::getLanguage()->languageID;
		}
		
		// if data is not multilingual, a language id has to be given
		if (!$this->parameters['data']['isMultilingual'] && !$this->parameters['data']['languageID']) {
			throw new UserInputException('languageID');
		}
		
		// check language id
		if ($this->parameters['data']['languageID'] && !LanguageFactory::getInstance()->getLanguage($this->parameters['data']['languageID'])) {
			throw new UserInputException('languageID');
		}
		
		// check category id
		if ($this->parameters['data']['categoryID']) {
			$category = CategoryHandler::getInstance()->getCategory($this->parameters['data']['categoryID']);
			if ($category === null || $category->getObjectType()->objectType !== 'com.woltlab.wcf.media.category') {
				throw new UserInputException('categoryID');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		if (isset($this->parameters['data']['categoryID']) && $this->parameters['data']['categoryID'] === 0) {
			$this->parameters['data']['categoryID'] = null;
		}
		
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
					isset($this->parameters['title'][$languageID]) ? mb_substr($this->parameters['title'][$languageID], 0, 255) : '',
					isset($this->parameters['caption'][$languageID]) ? $this->parameters['caption'][$languageID] : '',
					isset($this->parameters['altText'][$languageID]) ? mb_substr($this->parameters['altText'][$languageID], 0, 255) : ''
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
						mb_substr($title, 0, 255),
						$caption,
						mb_substr($altText, 0, 255)
					]);
				}
			}
			
			if (!empty($this->parameters['aclValues'])) {
				SimpleAclHandler::getInstance()->setValues('com.woltlab.wcf.media', $media->mediaID, $this->parameters['aclValues']);
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
		$this->readInteger('categoryID', true);
		
		$this->readBoolean('imagesOnly', true);
		
		$this->readString('mode');
		if ($this->parameters['mode'] != 'editor' && $this->parameters['mode'] != 'select') {
			throw new UserInputException('mode');
		}
		
		$this->readInteger('pageNo', true);
		if (!$this->parameters['pageNo']) $this->parameters['pageNo'] = 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSearchResultList() {
		$mediaList = new MediaList();
		$mediaList->addSearchConditions($this->parameters['searchString']);
		if (WCF::getSession()->getPermission('admin.content.cms.canOnlyAccessOwnMedia')) {
			$mediaList->getConditionBuilder()->add('media.userID = ?', [WCF::getUser()->userID]);
		}
		if ($this->parameters['imagesOnly']) {
			$mediaList->getConditionBuilder()->add('media.isImage = ?', [1]);
		}
		if ($this->parameters['categoryID']) {
			$mediaList->getConditionBuilder()->add('media.categoryID = ?', [$this->parameters['categoryID']]);
		}
		$mediaList->sqlOrderBy = 'media.uploadTime DESC, media.mediaID DESC';
		$mediaList->sqlLimit = static::ITEMS_PER_MANAGER_DIALOG_PAGE;
		$mediaList->sqlOffset = ($this->parameters['pageNo'] - 1) * static::ITEMS_PER_MANAGER_DIALOG_PAGE;
		$mediaList->readObjectIDs();
		
		if (empty($mediaList->getObjectIDs())) {
			// check if page is requested that might have existed but does not exist anymore due to deleted
			// media files
			if ($this->parameters['pageNo'] > 1 && $this->parameters['searchString'] === '' && !$this->parameters['categoryID']) {
				// request media dialog page with highest page number 
				$parameters = $this->parameters;
				$parameters['pageNo'] = ceil($mediaList->countObjects() / static::ITEMS_PER_MANAGER_DIALOG_PAGE);
				
				return (new MediaAction($this->objects, 'getSearchResultList', $parameters))->executeAction()['returnValues'];
			}
			
			return [
				'template' => WCF::getLanguage()->getDynamicVariable('wcf.media.search.noResults')
			];
		}
		
		$viewableMediaList = new ViewableMediaList();
		$viewableMediaList->setObjectIDs($mediaList->getObjectIDs());
		$viewableMediaList->readObjects();
		
		return [
			'media' => $this->getI18nMediaData($viewableMediaList),
			'pageCount' => ceil($mediaList->countObjects() / static::ITEMS_PER_MANAGER_DIALOG_PAGE),
			'pageNo' => $this->parameters['pageNo'],
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
		
		if (WCF::getSession()->getPermission('admin.content.cms.canOnlyAccessOwnMedia')) {
			foreach ($this->getObjects() as $media) {
				if ($media->userID != WCF::getUser()->userID) {
					throw new PermissionDeniedException();
				}
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
	
	/**
	 * Validates the `getSetCategoryDialog` action.
	 * 
	 * @throws	PermissionDeniedException	if user is not allowed to set category of media files
	 * @throws	IllegalLinkException		if no media file categories exist
	 */
	public function validateGetSetCategoryDialog() {
		if (!WCF::getSession()->getPermission('admin.content.cms.canManageMedia')) {
			throw new PermissionDeniedException();
		}
		
		if (empty(CategoryHandler::getInstance()->getCategories('com.woltlab.wcf.media.category'))) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Returns the dialog to set the category of multiple media files.
	 * 
	 * @return	string[]
	 */
	public function getSetCategoryDialog() {
		$categoryList = (new CategoryNodeTree('com.woltlab.wcf.media.category'))->getIterator();
		$categoryList->setMaxDepth(0);
		
		return [
			'template' => WCF::getTPL()->fetch('__mediaSetCategoryDialog', 'wcf', [
				'categoryList' => $categoryList
			])
		];
	}
	
	/**
	 * Validates the `setCategory` action.
	 * 
	 * @throws	PermissionDeniedException	if user is not allowed to edit a requested media file
	 * @throws	UserInputException		if no object ids are given
	 */
	public function validateSetCategory() {
		$this->validateGetSetCategoryDialog();
		
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		if (WCF::getSession()->getPermission('admin.content.cms.canOnlyAccessOwnMedia')) {
			foreach ($this->getObjects() as $media) {
				if ($media->userID != WCF::getUser()->userID) {
					throw new PermissionDeniedException();
				}
			}
		}
		
		$this->readInteger('categoryID', true);
	}
	
	/**
	 * Sets the category of multiple media files.
	 */
	public function setCategory() {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('mediaID IN (?)', [$this->objectIDs]);
		
		$sql = "UPDATE	wcf" . WCF_N . "_media
			SET	categoryID = ?
			" . $conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge(
			[$this->parameters['categoryID'] ?: null],
			$conditionBuilder->getParameters()
		));
		
		$this->unmarkItems();
	}
	
	/**
	 * Validates the `replaceFile` action.
	 * 
	 * @since       5.3
	 */
	public function validateReplaceFile() {
		WCF::getSession()->checkPermissions(['admin.content.cms.canManageMedia']);
		
		$this->getSingleObject();
		
		/** @noinspection PhpUndefinedMethodInspection */
		$this->parameters['__files']->validateFiles(
			new MediaReplaceUploadFileValidationStrategy($this->getSingleObject()->getDecoratedObject())
		);
	}
	
	/**
	 * Replaces the actual file of a media file.
	 * 
	 * @return      array
	 * @since       5.3
	 */
	public function replaceFile() {
		$saveStrategy = new DefaultUploadFileSaveStrategy(static::class, [
			'action' => 'update',
			'generateThumbnails' => true,
			'object' => $this->getSingleObject()->getDecoratedObject(),
			'rotateImages' => true,
		], [
			'fileUpdateTime' => TIME_NOW,
			'userID' => $this->getSingleObject()->userID,
			'username' => $this->getSingleObject()->username,
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
			$mediaList = new ViewableMediaList();
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
		
		// Delete *old* files using the non-updated local media editor object.
		if (empty($result['errors'])) {
			$this->getSingleObject()->deleteFiles();
		}
		
		return $result;
	}
}
