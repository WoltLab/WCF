<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\media\MediaAction;
use wcf\system\WCF;

/**
 * Clipboard action implementation for media files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.clipboard.action
 * @category	Community Framework
 * @since	2.2
 */
class MediaClipboardAction extends AbstractClipboardAction {
	/**
	 * @inheritdoc
	 */
	protected $actionClassActions = ['delete'];
	
	/**
	 * @inheritdoc
	 */
	protected $supportedActions = [
		'delete',
		'insert'
	];
	
	/**
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	public function getClassName() {
		return MediaAction::class;
	}
	
	/**
	 * @inheritdoc
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
		
		return array_keys($this->objects);
	}
	
	/**
	 * Returns the ids of the media files which can be inserted.
	 * 
	 * @return	integer[]
	 */
	public function validateInsert() {
		return array_keys($this->objects);
	}
}
