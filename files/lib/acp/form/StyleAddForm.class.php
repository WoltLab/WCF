<?php
namespace wcf\acp\form;
use wcf\data\package\Package;
use wcf\data\style\StyleAction;
use wcf\data\template\group\TemplateGroupList;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Shows the style add form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.acp.style
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class StyleAddForm extends ACPForm {
	/**
	 * @see	wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.style.add';
	
	/**
	 * author's name
	 * @var	string
	 */
	public $authorName = '';
	
	/**
	 * author's URL
	 * @var	string
	 */
	public $authorURL = '';
	
	/**
	 * list of available font families
	 * @var	array<string>
	 */
	public $availableFontFamilies = array(
		'Arial, Helvetica, sans-serif' => 'Arial',
		'Chicago, Impact, Compacta, sans-serif' => 'Chicago',
		'"Comic Sans MS", sans-serif' => 'Comic Sans',
		'"Courier New", Courier, monospace' => 'Courier New',
		'Geneva, Arial, Helvetica, sans-serif' => 'Geneva',
		'Georgia, "Times New Roman", Times, serif' => 'Georgia',
		'Helvetica, Verdana, sans-serif' => 'Helvetica',
		'Impact, Compacta, Chicago, sans-serif' => 'Impact',
		'"Lucida Sans", "Lucida Grande", Monaco, Geneva, sans-serif' => 'Lucida',
		'Tahoma, Arial, Helvetica, sans-serif' => 'Tahoma',
		'"Times New Roman", Times, Georgia, serif' => 'Times New Roman',
		'"Trebuchet MS", Arial, sans-serif' => 'Trebuchet MS',
		'Verdana, Helvetica, sans-serif' => 'Verdana'
	);
	
	/**
	 * list of available template groups
	 * @var	array<wcf\data\template\group\TemplateGroup>
	 */
	public $availableTemplateGroups = array();
	
	/**
	 * list of available units
	 * @var	array<string>
	 */
	public $availableUnits = array('px', 'em', '%', 'pt');
	
	/**
	 * list of color variables
	 * @var	array<string>
	 */
	public $colors = array();
	
	/**
	 * copyright message
	 * @var	string
	 */
	public $copyright = '';
	
	/**
	 * font family
	 * @var	string
	 */
	public $fontFamily = '';
	
	/**
	 * list of global variables
	 * 
	 * @var unknown_type
	 */
	public $globals = array();
	
	/**
	 * icon path
	 * @var	string
	 */
	public $iconPath = 'icon/';
	
	/**
	 * image path
	 * @var	string
	 */
	public $imagePath = 'images/';
	
	/**
	 * license name
	 * @var	string
	 */
	public $license = '';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.style.canAddStyle');
	
	/**
	 * last change date
	 * @var	string
	 */
	public $styleDate = '0000-00-00';
	
	/**
	 * description
	 * @var	string
	 */
	public $styleDescription = '';
	
	/**
	 * style name
	 * @var	string
	 */
	public $styleName = '';
	
	/**
	 * version number
	 * @var	string
	 */
	public $styleVersion = '';
	
	/**
	 * template group id
	 * @var	integer
	 */
	public $templateGroupID = 0;
	
	/**
	 * temporary image hash
	 * @var	string
	 */
	public $tmpHash = '';
	
	/**
	 * list of variables and their value
	 * @var	array<string>
	 */
	public $variables = array();
	
	/**
	 * list of specialized variables
	 * @var	array<string>
	 */
	public $specialVariables = array();
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->setVariables();
		$this->readStyleVariables();
		
		$templateGroupList = new TemplateGroupList();
		$templateGroupList->sqlLimit = 0;
		$templateGroupList->sqlOrderBy = "template_group.templateGroupName ASC";
		$templateGroupList->readObjects();
		$this->availableTemplateGroups = $templateGroupList->getObjects();
		
		if (isset($_REQUEST['tmpHash'])) {
			$this->tmpHash = StringUtil::trim($_REQUEST['tmpHash']);
		}
		if (empty($this->tmpHash)) {
			$this->tmpHash = StringUtil::getRandomID();
		}
	}
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		// ignore everything except well-formed rgba()
		$regEx = new Regex('rgba\(\d{1,3}, \d{1,3}, \d{1,3}, (1|1\.00?|0|0?\.[0-9]{1,2})\)');
		foreach ($this->colors as $variableName) {
			if (isset($_POST[$variableName]) && $regEx->match($_POST[$variableName])) {
				$this->variables[$variableName] = $_POST[$variableName];
			}
		}
		
		// read variables with units, e.g. 13px
		foreach ($this->globals as $variableName) {
			if (isset($_POST[$variableName]) && is_numeric($_POST[$variableName])) {
				if(isset($_POST[$variableName.'_unit']) && in_array($_POST[$variableName.'_unit'], $this->availableUnits)) { 
					$this->variables[$variableName] = $_POST[$variableName].$_POST[$variableName.'_unit'];
				}
			}
		}
		
		// read specialized variables
		foreach ($this->specialVariables as $variableName) {
			if (isset($_POST[$variableName])) $this->variables[$variableName] = StringUtil::trim($_POST[$variableName]);
		}
		$this->variables['useFluidLayout'] = (isset($_POST['useFluidLayout'])) ? 1 : 0;
		
		// style data
		if (isset($_POST['authorName'])) $this->authorName = StringUtil::trim($_POST['authorName']);
		if (isset($_POST['authorURL'])) $this->authorURL = StringUtil::trim($_POST['authorURL']);
		if (isset($_POST['copyright'])) $this->copyright = StringUtil::trim($_POST['copyright']);
		if (isset($_POST['iconPath'])) $this->iconPath = StringUtil::trim($_POST['iconPath']);
		if (isset($_POST['imagePath'])) $this->imagePath = StringUtil::trim($_POST['imagePath']);
		if (isset($_POST['license'])) $this->license = StringUtil::trim($_POST['license']);
		if (isset($_POST['styleDate'])) $this->styleDate = StringUtil::trim($_POST['styleDate']);
		if (isset($_POST['styleDescription'])) $this->styleDescription = StringUtil::trim($_POST['styleDescription']);
		if (isset($_POST['styleName'])) $this->styleName = StringUtil::trim($_POST['styleName']);
		if (isset($_POST['styleVersion'])) $this->styleVersion = StringUtil::trim($_POST['styleVersion']);
		if (isset($_POST['templateGroupID'])) $this->templateGroupID = intval($_POST['templateGroupID']);
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->authorName)) {
			throw new UserInputException('authorName');
		}
		
		if (empty($this->license)) {
			throw new UserInputException('license');
		}
		
		// validate date
		if (empty($this->styleDate)) {
			throw new UserInputException('styleDate');
		}
		else {
			try {
				DateUtil::validateDate($this->styleDate);
			}
			catch (SystemException $e) {
				throw new UserInputException('styleDate', 'notValid');
			}
		}
		
		if (empty($this->styleName)) {
			throw new UserInputException('styleName');
		}
		
		// validate version
		if (empty($this->styleVersion)) {
			throw new UserInputException('styleVersion');
		}
		else if (!Package::isValidVersion($this->styleVersion)) {
			throw new UserInputException('styleVersion', 'notValid');
		}
		
		// validate template group id
		if ($this->templateGroupID) {
			if (!isset($this->availableTemplateGroups[$this->templateGroupID])) {
				throw new UserInputException('templateGroupID');
			}
		}
		
		// ensure icon path is below WCF_DIR/icon/
		if ($this->iconPath) {
			$relativePath = FileUtil::unifyDirSeperator(FileUtil::getRelativePath(WCF_DIR.'icon/', WCF_DIR.$this->iconPath));
			if (strpos($relativePath, '../') !== false) {
				throw new UserInputException('iconPath', 'notValid');
			}
		}
		
		// ensure image path is below WCF_DIR/images/
		if ($this->imagePath) {
			$relativePath = FileUtil::unifyDirSeperator(FileUtil::getRelativePath(WCF_DIR.'images/', WCF_DIR.$this->imagePath));
			if (strpos($relativePath, '../') !== false) {
				throw new UserInputException('imagePath', 'notValid');
			}
		}
		
		if (!empty($this->variables['overrideLess'])) {
			$this->parseOverrides();
		}
	}
	
	/**
	 * Validates LESS-variable overrides.
	 * 
	 * If an override is invalid, unknown or matches a variable covered by
	 * the style editor itself, it will be silently discarded.
	 */
	protected function parseOverrides() {
		// get available variables
		$sql = "SELECT	variableName
			FROM	wcf".WCF_N."_style_variable";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$variables = array();
		while ($row = $statement->fetchArray()) {
			$variables[] = $row['variableName'];
		}
		
		$lines = explode("\n", StringUtil::unifyNewlines($this->variables['overrideLess']));
		$regEx = new Regex('^@([a-zA-Z]+): ?([@a-zA-Z0-9 ,\.\(\)\%]+);$');
		$errors = array();
		foreach ($lines as $index => &$line) {
			$line = StringUtil::trim($line);
			
			if ($regEx->match($line)) {
				$matches = $regEx->getMatches();
				
				// cannot override variables covered by style editor
				if (in_array($matches[1], $this->colors) || in_array($matches[1], $this->globals) || in_array($matches[1], $this->specialVariables)) {
					$errors[] = array(
						'error' => 'predefined',
						'text' => $matches[1]
					);
				}
				else if (!in_array($matches[1], $variables)) {
					// unknown style variable
					$errors[] = array(
						'error' => 'unknown',
						'text' => $matches[1]
					);
				}
				else {
					$this->variables[$matches[1]] = $matches[2];
				}
			}
			else {
				// not valid
				$errors[] = array(
					'error' => 'notValid',
					'text' => $line
				);
			}
		}
		
		$this->variables['overrideLess'] = implode("\n", $lines);
		
		if (!empty($errors)) {
			throw new UserInputException('overrideLess', $errors);
		}
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// parse global (unit) variables
		foreach ($this->globals as $variableName) {
			$unit = '';
			$value = $this->variables[$variableName];
			$i = strlen($value) - 1;
			while ($i >= 0) {
				$unit = $value[$i] . $unit;
				if (in_array($unit, $this->availableUnits)) {
					$this->variables[$variableName] = str_replace($unit, '', $value);
					$this->variables[$variableName.'_unit'] = $unit;
					break;
				}
				
				$i--;
			}
		}
	}
	
	/**
	 * Sets available variables
	 */
	protected function setVariables() {
		// set color variables
		$this->colors = array(
			'wcfButtonBackgroundColor',
			'wcfButtonBorderColor',
			'wcfButtonColor',
			'wcfButtonHoverBackgroundColor',
			'wcfButtonHoverBorderColor',
			'wcfButtonHoverColor',
			'wcfButtonPrimaryBackgroundColor',
			'wcfButtonPrimaryBorderColor',
			'wcfButtonPrimaryColor',
			'wcfColor',
			'wcfContainerAccentBackgroundColor',
			'wcfContainerBackgroundColor',
			'wcfContainerBorderColor',
			'wcfContainerHoverBackgroundColor',
			'wcfContentBackgroundColor',
			'wcfDimmedColor',
			'wcfInputBackgroundColor',
			'wcfInputBorderColor',
			'wcfInputColor',
			'wcfInputHoverBackgroundColor',
			'wcfInputHoverBorderColor',
			'wcfLinkColor',
			'wcfLinkHoverColor',
			'wcfPageBackgroundColor',
			'wcfPageColor',
			'wcfPageLinkColor',
			'wcfPageLinkHoverColor',
			'wcfTabularBoxBackgroundColor',
			'wcfTabularBoxColor',
			'wcfTabularBoxHoverColor',
			'wcfUserPanelBackgroundColor',
			'wcfUserPanelColor',
			'wcfUserPanelHoverBackgroundColor',
			'wcfUserPanelHoverColor',
		);
		
		// set global variables
		$this->globals = array(
			'wcfBaseFontSize',
			'wcfContainerBorderRadius',
			'wcfLayoutFixedWidth',
			'wcfLayoutFluidGap'
		);
		
		// set specialized variables
		$this->specialVariables = array(
			'individualLess',
			'overrideLess',
			'pageLogo',
			'useFluidLayout'
		);
		
		EventHandler::getInstance()->fireAction($this, 'setVariables');
	}
	
	/**
	 * Reads style variable values.
	 */
	protected function readStyleVariables() {
		$sql = "SELECT	variableName, defaultValue
			FROM	wcf".WCF_N."_style_variable";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$this->variables[$row['variableName']] = $row['defaultValue'];
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new StyleAction(array(), 'create', array(
			'data' => array(
				'styleName' => $this->styleName,
				'templateGroupID' => $this->templateGroupID,
				'disabled' => 1, // styles are disabled by default
				'styleDescription' => ($this->styleDescription ? $this->styleDescription : null),
				'styleVersion' => $this->styleVersion,
				'styleDate' => $this->styleDate,
				'imagePath' => $this->imagePath,
				'copyright' => $this->copyright,
				'license' => $this->license,
				'authorName' => $this->authorName,
				'authorURL' => $this->authorURL,
				'iconPath' => $this->iconPath
			),
			'tmpHash' => $this->tmpHash,
			'variables' => $this->variables
		));
		$this->objectAction->executeAction();
		
		// call saved event
		$this->saved();
		
		// reset variables
		$this->authorName = $this->authorURL = $this->copyright = $this->fontFamily = $this->image = '';
		$this->license = $this->styleDate = $this->styleDescription = $this->styleName = $this->styleVersion = '';
		
		$this->iconPath = 'icon/';
		$this->imagePath = 'images/';
		$this->templateGroupID = 0;
		
		// reload variables
		$this->readStyleVariables();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'authorName' => $this->authorName,
			'authorURL' => $this->authorURL,
			'availableFontFamilies' => $this->availableFontFamilies,
			'availableTemplateGroups' => $this->availableTemplateGroups,
			'availableUnits' => $this->availableUnits,
			'copyright' => $this->copyright,
			'fontFamily' => $this->fontFamily,
			'iconPath' => $this->iconPath,
			'imagePath' => $this->imagePath,
			'license' => $this->license,
			'styleDate' => $this->styleDate,
			'styleDescription' => $this->styleDescription,
			'styleName' => $this->styleName,
			'styleVersion' => $this->styleVersion,
			'templateGroupID' => $this->templateGroupID,
			'tmpHash' => $this->tmpHash,
			'variables' => $this->variables
		));
	}
}
