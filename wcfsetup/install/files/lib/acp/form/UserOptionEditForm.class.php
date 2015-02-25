<?php
namespace wcf\acp\form;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the user option edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserOptionEditForm extends UserOptionAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.option';
	
	/**
	 * user option id
	 * @var	integer
	 */
	public $optionID = 0;
	
	/**
	 * user option object
	 * @var	\wcf\data\user\option\UserOption
	 */
	public $userOption = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->optionID = intval($_REQUEST['id']);
		$this->userOption = new UserOption($this->optionID);
		if (!$this->userOption->optionID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		I18nHandler::getInstance()->save('optionName', 'wcf.user.option.'.$this->userOption->optionName, 'wcf.user.option');
		I18nHandler::getInstance()->save('optionDescription', 'wcf.user.option.'.$this->userOption->optionName.'.description', 'wcf.user.option');
		
		$this->objectAction = new UserOptionAction(array($this->userOption), 'update', array('data' => array_merge($this->additionalFields, array(
			'categoryName' => $this->categoryName,
			'optionType' => $this->optionType,
			'defaultValue' => $this->defaultValue,
			'showOrder' => $this->showOrder,
			'outputClass' => $this->outputClass,
			'validationPattern' => $this->validationPattern,
			'selectOptions' => $this->selectOptions,
			'required' => $this->required,
			'askDuringRegistration' => $this->askDuringRegistration,
			'searchable' => $this->searchable,
			'editable' => $this->editable,
			'visible' => $this->visible
		))));
		$this->objectAction->executeAction();
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		I18nHandler::getInstance()->setOptions('optionName', 1, 'wcf.user.option.'.$this->userOption->optionName, 'wcf.user.option.option\d+');
		I18nHandler::getInstance()->setOptions('optionDescription', 1, 'wcf.user.option.'.$this->userOption->optionName.'.description', 'wcf.user.option.option\d+.description');
		
		if (empty($_POST)) {
			$this->categoryName = $this->userOption->categoryName;
			$this->optionType = $this->userOption->optionType;
			$this->defaultValue = $this->userOption->defaultValue;
			$this->validationPattern = $this->userOption->validationPattern;
			$this->selectOptions = $this->userOption->selectOptions;
			$this->required = $this->userOption->required;
			$this->askDuringRegistration = $this->userOption->askDuringRegistration;
			$this->editable = $this->userOption->editable;
			$this->visible = $this->userOption->visible;
			$this->searchable = $this->userOption->searchable;
			$this->showOrder = $this->userOption->showOrder;
			$this->outputClass = $this->userOption->outputClass;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'optionID' => $this->optionID,
			'userOption' => $this->userOption
		));
	}
}
