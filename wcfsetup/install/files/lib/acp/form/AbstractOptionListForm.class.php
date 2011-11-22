<?php
namespace wcf\acp\form;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\option\OptionHandler;

/**
 * This class provides default implementations for a list of options.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
abstract class AbstractOptionListForm extends AbstractForm {
	/**
	 * @see	wcf\form\AbstractForm::$errorField
	 */
	public $errorField = array();
	
	/**
	 * @see	wcf\form\AbstractForm::$errorType
	 */
	public $errorType = array();
	
	/**
	 * cache name
	 * @var	string
	 */
	public $cacheName = 'option';
	
	/**
	 * cache class name
	 * @var string
	 */
	public $cacheClass = 'wcf\system\cache\builder\OptionCacheBuilder';
	
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
	 * true if active options are loaded when option handler is initialized
	 * @var	boolean
	 */
	public $loadActiveOptions = true;
	
	/**
	 * option handler object
	 * @var	wcf\system\option\IOptionHandler
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
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->optionHandler = new $this->optionHandlerClassName($this->cacheName, $this->cacheClass, $this->supportI18n, $this->languageItemPattern, $this->categoryName, $this->loadActiveOptions);
	}
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->optionHandler->readUserInput($_POST);
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->errorType = $this->optionHandler->validate();
		
		if (count($this->errorType) > 0) {
			throw new UserInputException('options', $this->errorType);
		}
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->optionHandler->readData();
		}
	}
}
