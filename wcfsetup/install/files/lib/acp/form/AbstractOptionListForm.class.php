<?php
namespace wcf\acp\form;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\option\OptionHandler;

/**
 * This class provides default implementations for a list of options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
abstract class AbstractOptionListForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $errorField = [];
	
	/**
	 * @inheritDoc
	 */
	public $errorType = [];
	
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
	public $optionHandlerClassName = OptionHandler::class;
	
	/**
	 * true if option supports i18n
	 * @var	boolean
	 */
	public $supportI18n = true;
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->optionHandler->readUserInput($_POST);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		$this->errorType = array_merge($this->optionHandler->validate(), $this->errorType);
		
		parent::validate();
		
		if (!empty($this->errorType)) {
			throw new UserInputException('options', $this->errorType);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->optionHandler->readData();
		}
	}
}
