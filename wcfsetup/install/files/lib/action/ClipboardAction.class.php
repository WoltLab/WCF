<?php
namespace wcf\action;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Handles clipboard items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
class ClipboardAction extends AJAXInvokeAction {
	/**
	 * clipboard action
	 * @var	string
	 */
	protected $action = '';
	
	/**
	 * list of allowed action methods
	 * @var	array<string>
	 */
	protected $allowedActions = array('mark', 'unmark', 'unmarkAll');
	
	/**
	 * container data
	 * @var	array
	 */
	protected $containerData = array();
	
	/**
	 * list of object ids
	 * @var	array<integer>
	 */
	protected $objectIDs = array();
	
	/**
	 * clipboard page class name
	 * @var	string
	 */
	protected $pageClassName = '';
	
	/**
	 * page object id
	 * @var	integer
	 */
	protected $pageObjectID = 0;
	
	/**
	 * object type
	 * @var	string
	 */
	protected $type = '';
	
	/**
	 * object type id
	 * @var	integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * @see	\wcf\action\Action::readParameters()
	 */
	public function readParameters() {
		AbstractSecureAction::readParameters();
		
		if (isset($_POST['action'])) $this->action = StringUtil::trim($_POST['action']);
		if (isset($_POST['containerData']) && is_array($_POST['containerData'])) $this->containerData = $_POST['containerData'];
		if (isset($_POST['objectIDs']) && is_array($_POST['objectIDs'])) $this->objectIDs = ArrayUtil::toIntegerArray($_POST['objectIDs']);
		if (isset($_POST['pageClassName'])) $this->pageClassName = StringUtil::trim($_POST['pageClassName']);
		if (isset($_POST['pageObjectID'])) $this->pageObjectID = intval($_POST['pageObjectID']);
		if (isset($_POST['type'])) $this->type = StringUtil::trim($_POST['type']);
	}
	
	/**
	 * @see	\wcf\action\Action::execute()
	 */
	public function execute() {
		AbstractSecureAction::execute();
		
		// execute clipboard action
		$this->executeAction();
		
		// get editor items
		$returnValues = $this->getEditorItems();
		// send JSON response
		header('Content-type: application/json');
		echo JSON::encode($returnValues);
		exit;
	}
	
	/**
	 * Executes clipboard action.
	 */
	protected function executeAction() {
		// validate parameters
		$this->validate();
		
		// execute action
		if ($this->action == 'unmarkAll') {
			ClipboardHandler::getInstance()->unmarkAll($this->objectTypeID);
		}
		else {
			ClipboardHandler::getInstance()->{$this->action}($this->objectIDs, $this->objectTypeID);
		}
	}
	
	/**
	 * Returns a list of clipboard editor items grouped by type name.
	 * 
	 * @return	array<array>
	 */
	protected function getEditorItems() {
		$data = ClipboardHandler::getInstance()->getEditorItems($this->pageClassName, $this->pageObjectID, $this->containerData);
		
		if ($data === null) {
			return array();
		}
		
		$editorItems = array();
		foreach ($data as $typeName => $itemData) {
			$items = array(
				'label' => $itemData['label'],
				'items' => array()
			);
			
			foreach ($itemData['items'] as $showOrder => $item) {
				$items['items'][$showOrder] = array(
					'actionName' => $item->getName(),
					'internalData' => $item->getInternalData(),
					'parameters' => $item->getParameters(),
					'label' => WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.' . $item->getName(), array('count' => $item->getCount())),
					'url' => $item->getURL()
				);
			}
			
			$editorItems[$typeName] = $items;
		}
		
		return array(
			'action' => $this->action,
			'items' => $editorItems
		);
	}
	
	/**
	 * Validates parameters.
	 */
	protected function validate() {
		if (!in_array($this->action, $this->allowedActions)) {
			throw new UserInputException('action');
		}
		
		if ($this->action != 'unmarkAll') {
			if (empty($this->objectIDs)) {
				throw new UserInputException('objectIDs');
			}
			
			if (empty($this->pageClassName)) {
				throw new UserInputException('pageClassName');
			}
		}
		
		$this->objectTypeID = (!empty($this->type)) ? ClipboardHandler::getInstance()->getObjectTypeID($this->type) : null;
		if ($this->objectTypeID === null) {
			throw new UserInputException('type');
		}
	}
}
