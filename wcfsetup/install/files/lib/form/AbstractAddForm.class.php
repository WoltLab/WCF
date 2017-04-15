<?php
namespace wcf\form;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Abstract implementation for add Forms.
 * 
 * @author	Jeffrey Reichardt
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
abstract class AbstractAddForm extends AbstractForm {
	/**
	 * @see	wcf\page\AbstractPage::$action
	 */
	public $action = 'add';

	/**
	 * Name of action class
	 * @var string
	 */
	public $actionClassName = '';

	/**
	 * holds fieldNames and default values
	 * @var	array
	 */
	public $fields = array();

	/**
	 * Holds values of $_POST
	 * @var array
	 */
	public $values = array();

	/**
	 * Holds required fields
	 * @var	array
	 */
	public $validateFields = array();
	
	/**
	 * @see	wcf\form\Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		foreach ($this->fields as $key => $value) {
			if (isset($_POST[$key])) $this->values[$key] = $_POST[$key];
		}
	}

	/**
	 * @see	wcf\form\Form::validate()
	 */
	public function validate() {
		parent::validate();

		foreach ($this->validateFields as $fieldName) {
			if (!isset($this->values[$fieldName]) || empty($this->values[$fieldName])) {
				throw new UserInputException($fieldName);
			}
		}
	}

	/**
	 * @see	wcf\form\Form::save()
	 */
	public function save() {
		parent::save();

		if (empty($this->actionClassName)) {
			throw new SystemException('Member $actionClassName not defined for '.get_class($this));
		}

		$this->objectAction = new $this->actionClassName(array(), 'create', array('data' => $this->values));
		$this->objectAction->executeAction();
		
		// saved
		$this->saved();
		
		// reset values
		$this->resetFieldValues();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}

	/**
	 * Resets field values.
	 */
	public function resetFieldValues() {
		foreach ($this->fields as $key => $value) {
			if (isset($this->values[$key])) {
				$this->values[$key] = $this->fields[$key];
			}
		}
	}

	/**
	 * @see	wcf\page\Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array_merge($this->fields, $this->values));
	}
}
