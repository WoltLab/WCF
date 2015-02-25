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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language.item
 * @category	Community Framework
 */
class LanguageItemAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\language\item\LanguageItemEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.language.canManageLanguage');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.language.canManageLanguage');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.language.canManageLanguage');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('create', 'delete', 'edit', 'prepareEdit', 'update');
	
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
		WCF::getTPL()->assign(array(
			'item' => $item
		));
		
		return array(
			'languageItem' => $item->languageItem,
			'template' => WCF::getTPL()->fetch('languageItemEditDialog')
		);
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
			$updateData = array(
				'languageCustomItemValue' => !$this->parameters['languageUseCustomValue'] && empty($this->parameters['languageCustomItemValue']) ? null : $this->parameters['languageCustomItemValue'],
				'languageUseCustomValue' => ($this->parameters['languageUseCustomValue'] ? 1 : 0)
			);
		}
		else {
			$updateData = array(
				'languageItemValue' => $this->parameters['languageItemValue']
			);
		}
		$editor->update($updateData);
		
		// clear cache
		LanguageFactory::getInstance()->clearCache();
		LanguageFactory::getInstance()->deleteLanguageCache();
	}
}
