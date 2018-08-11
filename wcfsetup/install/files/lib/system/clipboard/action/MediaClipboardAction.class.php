<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\media\Media;
use wcf\data\media\MediaAction;
use wcf\system\category\CategoryHandler;
use wcf\system\WCF;

/**
 * Clipboard action implementation for media files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Clipboard\Action
 * @since	3.0
 */
class MediaClipboardAction extends AbstractClipboardAction {
	/**
	 * @inheritDoc
	 */
	protected $actionClassActions = ['delete'];
	
	/**
	 * @inheritDoc
	 */
	protected $supportedActions = [
		'delete',
		'insert',
		'setCategory'
	];
	
	/**
	 * @inheritDoc
	 */
	public function execute(array $objects, ClipboardAction $action) {
		$item = parent::execute($objects, $action);
		
		if ($item === null) {
			return null;
		}
		
		// handle actions
		switch ($action->actionName) {
			case 'delete':
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.woltlab.wcf.media.delete.confirmMessage', [
					'count' => $item->getCount()
				]));
			break;
		}
		
		return $item;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getClassName() {
		return MediaAction::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTypeName() {
		return 'com.woltlab.wcf.media';
	}
	
	/**
	 * Returns the ids of the media files which can be deleted.
	 * 
	 * @return	integer[]
	 */
	public function validateDelete() {
		if (!WCF::getSession()->getPermission('admin.content.cms.canManageMedia')) {
			return [];
		}
		
		$mediaIDs = array_keys($this->objects);
		
		if (WCF::getSession()->getPermission('admin.content.cms.canOnlyAccessOwnMedia')) {
			$mediaIDs = [];
			
			/** @var Media $media */
			foreach ($this->objects as $media) {
				if ($media->userID == WCF::getUser()->userID) {
					$mediaIDs[] = $media->mediaID;
				}
			}
		}
		
		return $mediaIDs;
	}
	
	/**
	 * Returns the ids of the media files which can be inserted.
	 * 
	 * @return	integer[]
	 */
	public function validateInsert() {
		return array_keys($this->objects);
	}
	
	/**
	 * Returns the ids of the media files whose category can be set.
	 * 
	 * @return	integer[]
	 */
	public function validateSetCategory() {
		if (!WCF::getSession()->getPermission('admin.content.cms.canManageMedia')) {
			return [];
		}
		
		// category can only be set if any category exists
		if (empty(CategoryHandler::getInstance()->getCategories('com.woltlab.wcf.media.category'))) {
			return [];
		}
		
		$mediaIDs = array_keys($this->objects);
		
		if (WCF::getSession()->getPermission('admin.content.cms.canOnlyAccessOwnMedia')) {
			$mediaIDs = [];
			
			/** @var Media $media */
			foreach ($this->objects as $media) {
				if ($media->userID == WCF::getUser()->userID) {
					$mediaIDs[] = $media->mediaID;
				}
			}
		}
		
		return $mediaIDs;
	}
}
