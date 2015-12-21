<?php
namespace wcf\acp\form;
use wcf\data\box\Box;
use wcf\data\box\BoxAction;
use wcf\data\box\BoxEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the box add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 * @since	2.2
 */
class BoxAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.box.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManageBox'];
	
	/**
	 * true if created box is multi-lingual
	 * @var boolean
	 */
	public $isMultilingual = 0;
	
	/**
	 * box type
	 * @var string
	 */
	public $boxType = '';
	
	/**
	 * box position
	 * @var string
	 */
	public $position = '';
	
	/**
	 * show order
	 * @var integer
	 */
	public $showOrder = 0;
	
	/**
	 * true if created box is visible everywhere 
	 * @var boolean
	 */
	public $visibleEverywhere = 1;
	
	/**
	 * css class name of created box
	 * @var string
	 */
	public $cssClassName = '';
	
	/**
	 * true if box header is visible
	 * @var boolean
	 */
	public $showHeader = 1;
	
	/**
	 * php class name
	 * @var string
	 */
	public $className = '';
	
	/**
	 * box name
	 * @var string
	 */
	public $name = '';
	
	/**
	 * page titles
	 * @var array<string>
	 */
	public $title = [];
	
	/**
	 * page contents
	 * @var array<string>
	 */
	public $content = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
	
		if (!empty($_REQUEST['isMultilingual'])) $this->isMultilingual = 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->visibleEverywhere = $this->showOrder = 0;
		if (isset($_POST['name'])) $this->name = StringUtil::trim($_POST['name']);
		if (isset($_POST['boxType'])) $this->boxType = $_POST['boxType'];
		if (isset($_POST['position'])) $this->position = $_POST['position'];
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['visibleEverywhere'])) $this->visibleEverywhere = intval($_POST['visibleEverywhere']);
		if (isset($_POST['cssClassName'])) $this->cssClassName = StringUtil::trim($_POST['cssClassName']);
		if (isset($_POST['showHeader'])) $this->showHeader = intval($_POST['showHeader']);
		if (isset($_POST['className'])) $this->className = StringUtil::trim($_POST['className']);
		
		if (isset($_POST['title']) && is_array($_POST['title'])) $this->title = ArrayUtil::trim($_POST['title']);
		if (isset($_POST['content']) && is_array($_POST['content'])) $this->content = ArrayUtil::trim($_POST['content']);
		
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// validate name
		$this->validateName();
		
		// validate box type
		if (!in_array($this->boxType, Box::$availableBoxTypes)) {
			throw new UserInputException('boxType');
		}
		
		// validate box position
		if (!in_array($this->position, Box::$availablePositions)) {
			throw new UserInputException('position');
		}
		
		// validate class name
		if ($this->boxType == 'system') {
			if (empty($this->className)) {
				throw new UserInputException('className');
			}
			
			// @todo check class
			
		}
	}
	
	/**
	 * Validates box name.
	 */
	protected function validateName() {
		if (empty($this->name)) {
			throw new UserInputException('name');
		}
		if (Box::getBoxByName($this->name)) {
			throw new UserInputException('name', 'notUnique');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$content = [];
		if ($this->isMultilingual) {
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$content[$language->languageID] = [
					'title' => (!empty($_POST['title'][$language->languageID]) ? $_POST['title'][$language->languageID] : ''),
					'content' => (!empty($_POST['content'][$language->languageID]) ? $_POST['content'][$language->languageID] : '')
				];
			}
		}
		else {
			$content[0] = [
				'title' => (!empty($_POST['title'][0]) ? $_POST['title'][0] : ''),
				'content' => (!empty($_POST['content'][0]) ? $_POST['content'][0] : '')
			];
		}
		
		$this->objectAction = new BoxAction([], 'create', ['data' => array_merge($this->additionalFields, [
			'name' => $this->name,
			'packageID' => 1,
			'isMultilingual' => $this->isMultilingual,
			'boxType' => $this->boxType,
			'position' => $this->position,
			'showOrder' => $this->showOrder,
			'visibleEverywhere' => $this->visibleEverywhere,
			'cssClassName' => $this->cssClassName,
			'showHeader' => $this->showHeader,			
			'className' => $this->className,
			'identifier' => ''
		]), 'content' => $content]);
		$returnValues = $this->objectAction->executeAction();
		// set generic box identifier
		$boxEditor = new BoxEditor($returnValues['returnValues']);
		$boxEditor->update([
			'identifier' => 'com.woltlab.wcf.generic'.$boxEditor->boxID
		]);
		
		// call saved event
		$this->saved();
		
		// show success
		WCF::getTPL()->assign('success', true);
		
		// reset variables
		$this->boxType = $this->position = $this->cssClassName = $this->className = $this->name = '';
		$this->showOrder = 0;
		$this->visibleEverywhere = $this->showHeader = 1;
		$this->title = $this->content = [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'add',
			'isMultilingual' => $this->isMultilingual,
			'name' => $this->name,
			'boxType' => $this->boxType,
			'position' => $this->position,
			'cssClassName' => $this->cssClassName,
			'className' => $this->className,
			'showOrder' => $this->showOrder,
			'visibleEverywhere' => $this->visibleEverywhere,
			'showHeader' => $this->showHeader,
			'title' => $this->title,
			'content' => $this->content,
			'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
			'availableBoxTypes' => Box::$availableBoxTypes,
			'availablePositions' => Box::$availablePositions
		]);
	}
}
