<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\system\clipboard\ClipboardEditorItem;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Abstract implementation of a clipboard action handler.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.clipboard.action
 * @category	Community Framework
 */
abstract class AbstractClipboardAction implements IClipboardAction {
	/**
	 * list of the clipboard actions which are executed by the action class
	 * @var	array<string>
	 */
	protected $actionClassActions = array();
	
	/**
	 * relevant database objects
	 * @var	array<\wcf\data\DatabaseObject>
	 */
	protected $objects = array();
	
	/**
	 * list of the supported clipboard actions
	 * @var	array<string>
	 */
	protected $supportedActions = array();
	
	/**
	 * @see	\wcf\system\clipboard\action\IClipboardAction::execute()
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
	 * @see	\wcf\system\clipboard\action\IClipboardAction::filterObjects()
	 */
	public function filterObjects(array $objects, array $typeData) {
		return $objects;
	}
	
	/**
	 * @see	\wcf\system\clipboard\action\IClipboardAction::getEditorLabel()
	 */
	public function getEditorLabel(array $objects) {
		return WCF::getLanguage()->getDynamicVariable('wcf.clipboard.label.'.$this->getTypeName().'.marked', array(
			'count' => count($objects)
		));
	}
}
