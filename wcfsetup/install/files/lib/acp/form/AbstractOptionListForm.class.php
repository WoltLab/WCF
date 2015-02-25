<?php
namespace wcf\acp\form;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;

/**
 * This class provides default implementations for a list of options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
abstract class AbstractOptionListForm extends AbstractForm {
	/**
	 * @see	\wcf\form\AbstractForm::$errorField
	 */
	public $errorField = array();
	
	/**
	 * @see	\wcf\form\AbstractForm::$errorType
	 */
	public $errorType = array();
	
	/**
	 * name of the active option category
	 * @var	string
	 */
	public $categoryName = '';
	
	/**
	 * language item pattern
	 * @var	string
	 */
	protected $languageItemPattern = '';
	
	/**
	 * option handler object
	 * @var	\wcf\system\option\IOptionHandler
	 */
	public $optionHandler = null;
	
	/**
	 * option handler class name
	 * @var	string
	 */
	public $optionHandlerClassName = 'wcf\system\option\OptionHandler';
	
	/**
	 * true if option supports i18n
	 * @var	boolean
	 */
	public $supportI18n = true;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->optionHandler = new $this->optionHandlerClassName($this->supportI18n, $this->languageItemPattern, $this->categoryName);
		$this->initOptionHandler();
	}
	
	/**
	 * Initializes the option handler.
	 */
	protected function initOptionHandler() {
		$this->optionHandler->init();
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->optionHandler->readUserInput($_POST);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		$this->errorType = array_merge($this->optionHandler->validate(), $this->errorType);
		
		parent::validate();
		
		if (!empty($this->errorType)) {
			throw new UserInputException('options', $this->errorType);
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->optionHandler->readData();
		}
	}
}
