<?php
namespace wcf\acp\form;
use wcf\data\label\Label;
use wcf\data\label\LabelAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the label edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class LabelEditForm extends LabelAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.label';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.label.canManageLabel'];
	
	/**
	 * label id
	 * @var	integer
	 */
	public $labelID = 0;
	
	/**
	 * label object
	 * @var	\wcf\data\label\Label
	 */
	public $labelObj = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->labelID = intval($_REQUEST['id']);
		$this->labelObj = new Label($this->labelID);
		if (!$this->labelObj->labelID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$this->label = 'wcf.acp.label.label'.$this->labelObj->labelID;
		if (I18nHandler::getInstance()->isPlainValue('label')) {
			I18nHandler::getInstance()->remove($this->label);
			$this->label = I18nHandler::getInstance()->getValue('label');
		}
		else {
			I18nHandler::getInstance()->save('label', $this->label, 'wcf.acp.label', 1);
		}
		
		// update label
		$this->objectAction = new LabelAction([$this->labelID], 'update', ['data' => array_merge($this->additionalFields, [
			'label' => $this->label,
			'cssClassName' => ($this->cssClassName == 'custom' ? $this->customCssClassName : $this->cssClassName),
			'groupID' => $this->groupID,
			'showOrder' => $this->showOrder
		])]);
		$this->objectAction->executeAction();
		
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.label.objectType');
		foreach ($objectTypes as $objectType) {
			$objectType->getProcessor()->save();
		}
		
		$this->saved();
		
		// reset values if non-custom value was choosen
		if ($this->cssClassName != 'custom') $this->customCssClassName = '';
		
		// show success
		WCF::getTPL()->assign([
			'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('label', 1, $this->labelObj->label, 'wcf.acp.label.label\d+');
			$this->label = $this->labelObj->label;
			
			$this->cssClassName = $this->labelObj->cssClassName;
			if (!in_array($this->cssClassName, $this->availableCssClassNames)) {
				$this->customCssClassName = $this->cssClassName;
				$this->cssClassName = 'custom';
			}
			
			$this->groupID = $this->labelObj->groupID;
			$this->showOrder = $this->labelObj->showOrder;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign([
			'label' => $this->labelObj,
			'action' => 'edit'
		]);
	}
}
