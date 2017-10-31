<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\DatabaseObject;
use wcf\system\clipboard\ClipboardEditorItem;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Abstract implementation of a clipboard action handler.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Clipboard\Action
 */
abstract class AbstractClipboardAction implements IClipboardAction {
	/**
	 * list of the clipboard actions which are executed by the action class
	 * @var	string[]
	 */
	protected $actionClassActions = [];
	
	/**
	 * relevant database objects
	 * @var	DatabaseObject[]
	 */
	protected $objects = [];
	
	/**
	 * list of the supported clipboard actions
	 * @var	string[]
	 */
	protected $supportedActions = [];
	
	/**
	 * list of clipboard actions which need a reload of the page after execution
	 * @var	string[]
	 */
	protected $refreshPageActions = [];
	
	/**
	 * @inheritDoc
	 */
	public function execute(array $objects, ClipboardAction $action) {
		if (!in_array($action->actionName, $this->supportedActions)) {
			throw new SystemException("Unknown clipboard action '".$action->actionName."'");
		}
		
		$this->objects = $objects;
		
		$item = new ClipboardEditorItem();
		$item->setName($this->getTypeName().'.'.$action->actionName);
		
		// set action class-related data
		if (in_array($action->actionName, $this->actionClassActions)) {
			$item->addParameter('actionName', $action->actionName);
			$item->addParameter('className', $this->getClassName());
			$item->addParameter('refreshPageAfterExecution', in_array($action->actionName, $this->refreshPageActions) ? 'true' : 'false');
		}
		
		// validate objects if relevant method exists and set valid object ids
		$methodName = 'validate'.ucfirst($action->actionName);
		if (method_exists($this, $methodName)) {
			$objectIDs = $this->$methodName();
			if (empty($objectIDs)) {
				return null;
			}
			
			$item->addParameter('objectIDs', $objectIDs);
		}
		
		return $item;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEditorLabel(array $objects) {
		return WCF::getLanguage()->getDynamicVariable('wcf.clipboard.label.'.$this->getTypeName().'.marked', [
			'count' => count($objects)
		]);
	}
}
