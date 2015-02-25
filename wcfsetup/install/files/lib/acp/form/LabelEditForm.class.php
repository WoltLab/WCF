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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class LabelEditForm extends LabelAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.label';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.label.canManageLabel');
	
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
	 * @see	\wcf\page\IPage::readParameters()
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
	 * @see	\wcf\form\IForm::save()
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
		$this->objectAction = new LabelAction(array($this->labelID), 'update', array('data' => array_merge($this->additionalFields, array(
			'label' => $this->label,
			'cssClassName' => ($this->cssClassName == 'custom' ? $this->customCssClassName : $this->cssClassName),
			'groupID' => $this->groupID
		))));
		$this->objectAction->executeAction();
		
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.label.objectType');
		foreach ($objectTypes as $objectType) {
			$objectType->getProcessor()->save();
		}
		
		$this->saved();
		
		// reset values if non-custom value was choosen
		if ($this->cssClassName != 'custom') $this->customCssClassName = '';
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
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
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign(array(
			'label' => $this->labelObj,
			'action' => 'edit'
		));
	}
}
