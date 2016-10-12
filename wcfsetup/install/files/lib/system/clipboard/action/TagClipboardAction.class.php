<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\tag\TagAction;
use wcf\system\WCF;

/**
 * Clipboard action implementation for tags.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Clipboard\Action
 * @since	3.0
 */
class TagClipboardAction extends AbstractClipboardAction {
	/**
	 * @inheritDoc
	 */
	protected $actionClassActions = ['delete'];
	
	/**
	 * @inheritDoc
	 */
	protected $supportedActions = ['delete', 'setAsSynonyms'];
	
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
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.woltlab.wcf.tag.delete.confirmMessage', [
					'count' => $item->getCount()
				]));
			break;
			
			case 'setAsSynonyms':
				$item->addParameter('template', WCF::getTPL()->fetch('tagSetAsSynonyms', 'wcf', [
					'tags' => $this->objects
				]));
			break;
		}
		
		return $item;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getClassName() {
		return TagAction::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTypeName() {
		return 'com.woltlab.wcf.tag';
	}
	
	/**
	 * Returns the ids of the tags which can be deleted.
	 * 
	 * @return	integer[]
	 */
	protected function validateDelete() {
		if (!WCF::getSession()->getPermission('admin.content.tag.canManageTag')) {
			return [];
		}
		
		return array_keys($this->objects);
	}
	
	/**
	 * Returns the ids of the tags which can be set as synonyms.
	 * 
	 * @return	integer[]
	 */
	protected function validateSetAsSynonyms() {
		if (!WCF::getSession()->getPermission('admin.content.tag.canManageTag')) {
			return [];
		}
		
		if (count($this->objects) < 2) {
			return [];
		}
		
		return array_keys($this->objects);
	}
}
