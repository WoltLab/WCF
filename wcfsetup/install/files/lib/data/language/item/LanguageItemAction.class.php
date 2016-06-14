<?php
namespace wcf\data\language\item;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Executes language item-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Language\Item
 * 
 * @method	LanguageItem		create()
 * @method	LanguageItemEditor[]	getObjects()
 * @method	LanguageItemEditor	getSingleObject()
 */
class LanguageItemAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = LanguageItemEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.language.canManageLanguage'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.language.canManageLanguage'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.language.canManageLanguage'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'edit', 'prepareEdit', 'update'];
	
	/**
	 * Validates parameters to prepare edit.
	 */
	public function validatePrepareEdit() {
		if (!WCF::getSession()->getPermission('admin.language.canManageLanguage')) {
			throw new PermissionDeniedException();
		}
		
		$this->readObjects();
		if (!count($this->objects)) {
			throw new UserInputException('objectIDs');
		}
	}
	
	/**
	 * Prepares edit.
	 */
	public function prepareEdit() {
		$item = reset($this->objects);
		WCF::getTPL()->assign([
			'item' => $item
		]);
		
		return [
			'languageItem' => $item->languageItem,
			'template' => WCF::getTPL()->fetch('languageItemEditDialog')
		];
	}
	
	/**
	 * Validates edit action.
	 */
	public function validateEdit() {
		if (!WCF::getSession()->getPermission('admin.language.canManageLanguage')) {
			throw new PermissionDeniedException();
		}
		
		$this->readObjects();
		if (!count($this->objects)) {
			throw new UserInputException('objectIDs');
		}
		
		$this->readString('languageItemValue', true);
		$this->readString('languageCustomItemValue', true);
		$this->readBoolean('languageUseCustomValue', true);
	}
	
	/**
	 * Edits an item.
	 */
	public function edit() {
		// save item
		$editor = reset($this->objects);
		if ($editor->languageItemOriginIsSystem) {
			$updateData = [
				'languageCustomItemValue' => !$this->parameters['languageUseCustomValue'] && empty($this->parameters['languageCustomItemValue']) ? null : $this->parameters['languageCustomItemValue'],
				'languageUseCustomValue' => ($this->parameters['languageUseCustomValue'] ? 1 : 0)
			];
		}
		else {
			$updateData = [
				'languageItemValue' => $this->parameters['languageItemValue']
			];
		}
		$editor->update($updateData);
		
		// clear cache
		LanguageFactory::getInstance()->clearCache();
		LanguageFactory::getInstance()->deleteLanguageCache();
	}
}
