<?php
namespace wcf\data\clipboard\item;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Clipboard API handler.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.clipboard.item
 * @category	Community Framework
 * @since	2.2
 */
class ClipboardItemAction extends AbstractDatabaseObjectAction {
	/**
	 * object type id
	 * @var	integer
	 */
	public $objectTypeID = 0;
	
	/**
	 * This is a heavily modified constructor which behaves differently from other DBOActions,
	 * primarily because this class just masquerades as a regular DBOAction.
	 * 
	 * @see	\wcf\data\AbstractDatabaseObjectAction
	 */
	public function __construct(array $objects, $action, array $parameters = array()) {
		$this->action = $action;
		$this->parameters = $parameters;
		
		// fire event action
		EventHandler::getInstance()->fireAction($this, 'initializeAction');
	}
	
	/**
	 * Validates parameters to set an item as marked.
	 */
	public function validateMark() {
		$this->validateDefaultParameters();
		
		$this->readIntegerArray('objectIDs');
		
		$this->readObjectType();
	}
	
	/**
	 * Sets an item as marked.
	 * 
	 * @return	mixed[]
	 */
	public function mark() {
		ClipboardHandler::getInstance()->mark($this->parameters['objectIDs'], $this->objectTypeID);
		
		return $this->getEditorItems();
	}
	
	/**
	 * Validates parameters to unset an item as marked.
	 */
	public function validateUnmark() {
		$this->validateMark();
	}
	
	/**
	 * Unsets an item as marked.
	 * 
	 * @return	mixed[]
	 */
	public function unmark() {
		ClipboardHandler::getInstance()->unmark($this->parameters['objectIDs'], $this->objectTypeID);
		
		return $this->getEditorItems();
	}
	
	/**
	 * Validates parameters to fetch the list of marked items.
	 */
	public function validateGetMarkedItems() {
		$this->validateDefaultParameters();
	}
	
	/**
	 * Returns the list of marked items.
	 * 
	 * @return	mixed[]
	 */
	public function getMarkedItems() {
		return $this->getEditorItems();
	}
	
	/**
	 * Validates parameters to unmark all items of a type.
	 */
	public function validateUnmarkAll() {
		$this->readObjectType();
	}
	
	/**
	 * Unmarks all items of a type.
	 * 
	 * @return	string[]
	 */
	public function unmarkAll() {
		ClipboardHandler::getInstance()->unmarkAll($this->objectTypeID);
		
		return ['objectType' => $this->parameters['objectType']];
	}
	
	/**
	 * Validates generic parameters used for most clipboard actions.
	 */
	protected function validateDefaultParameters() {
		$this->readString('pageClassName');
		$this->readInteger('pageObjectID', true);
	}
	
	/**
	 * Reads the object type and sets the internal object type id.
	 */
	protected function readObjectType() {
		$this->readString('objectType', false);
		
		if (!empty($this->parameters['objectType'])) {
			$this->objectTypeID = ClipboardHandler::getInstance()->getObjectTypeID($this->parameters['objectType']);
			if ($this->objectTypeID === null) {
				throw new UserInputException('objectType');
			}
		}
	}
	
	/**
	 * Returns a list of clipboard editor items grouped by type name.
	 *
	 * @return	mixed[]
	 */
	protected function getEditorItems() {
		$data = ClipboardHandler::getInstance()->getEditorItems($this->parameters['pageClassName'], $this->parameters['pageObjectID']);
		
		if ($data === null) {
			return [];
		}
		
		$editorItems = [];
		foreach ($data as $typeName => $itemData) {
			$items = [
				'label' => $itemData['label'],
				'items' => []
			];
			
			foreach ($itemData['items'] as $showOrder => $item) {
				$items['items'][$showOrder] = [
					'actionName' => $item->getName(),
					'internalData' => $item->getInternalData(),
					'parameters' => $item->getParameters(),
					'label' => WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.' . $item->getName(), ['count' => $item->getCount()]),
					'url' => $item->getURL()
				];
			}
			
			$editorItems[$typeName] = $items;
		}
		
		$returnValues = [
			'action' => $this->action,
			'items' => $editorItems,
			'markedItems' => []
		];
		
		// break if no items are available (status was cached by browser)
		if (empty($returnValues['items'])) {
			return $returnValues;
		}
		
		// load marked items from runtime cache
		$data = ClipboardHandler::getInstance()->getMarkedItems();
		
		// insert object ids for each type of marked items
		$returnValues['markedItems'] = [];
		foreach ($data as $typeName => $itemData) {
			$returnValues['markedItems'][$typeName] = array_keys($itemData);
		}
		
		return $returnValues;
	}
}
