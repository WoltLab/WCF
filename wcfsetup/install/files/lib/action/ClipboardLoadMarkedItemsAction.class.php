<?php
namespace wcf\action;
use wcf\system\clipboard\ClipboardHandler;

/**
 * Handles marked clipboard items once DOM is loaded.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
class ClipboardLoadMarkedItemsAction extends ClipboardAction {
	/**
	 * @see	\wcf\action\ClipboardAction::executeAction()
	 */
	protected function executeAction() { }
	
	/**
	 * @see	\wcf\action\ClipboardAction::getEditorItems()
	 */
	protected function getEditorItems() {
		$returnValues = parent::getEditorItems();
		$returnValues['markedItems'] = array();
		
		// break if no items are available (status was cached by browser)
		if (empty($returnValues['items'])) {
			return $returnValues;
		}
		
		// load marked items from runtime cache
		$data = ClipboardHandler::getInstance()->getMarkedItems();
		
		// insert object ids for each type of marked items
		$returnValues['markedItems'] = array();
		foreach ($data as $typeName => $itemData) {
			$returnValues['markedItems'][$typeName] = array_keys($itemData);
		}
		
		return $returnValues;
	}
}
