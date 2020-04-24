<?php
namespace wcf\acp\form;
use wcf\data\package\Package;
use wcf\data\style\StyleAction;
use wcf\data\style\StyleEditor;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupList;
use wcf\form\AbstractForm;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Shows the style add form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class StyleAddForm extends AbstractForm {
	/**
	 * @inheritDoc
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
	 * @var	string[]
	 */
	public $availableFontFamilies = [
		'Arial, Helvetica, sans-serif' => 'Arial',
		'Chicago, Impact, Compacta, sans-serif' => 'Chicago',
		'"Comic Sans MS", sans-serif' => 'Comic Sans',
		'"Courier New", Courier, monospace' => 'Courier New',
		'Geneva, Arial, Helvetica, sans-serif' => 'Geneva',
		'Georgia, "Times New Roman", Times, serif' => 'Georgia',
		'Helvetica, Verdana, sans-serif' => 'Helvetica',
		'Impact, Compacta, Chicago, sans-serif' => 'Impact',
		'"Lucida Sans", "Lucida Grande", Monaco, Geneva, sans-serif' => 'Lucida',
		'"Segoe UI", "DejaVu Sans", "Lucida Grande", Helvetica, sans-serif' => 'Segoe UI',
		'Tahoma, Arial, Helvetica, sans-serif' => 'Tahoma',
		'"Times New Roman", Times, Georgia, serif' => 'Times New Roman',
		'"Trebuchet MS", Arial, sans-serif' => 'Trebuchet MS',
		'Verdana, Helvetica, sans-serif' => 'Verdana'
	];
	
	/**
	 * list of available template groups
	 * @var	TemplateGroup[]
	 */
	public $availableTemplateGroups = [];
	
	/**
	 * list of available units
	 * @var	string[]
	 */
	public $availableUnits = ['px', 'pt', 'rem', 'em', '%'];
	
	/**
	 * @var array
	 */
	public $colorCategories = [];
	
	/**
	 * list of color variables
	 * @var	string[][]
	 */
	public $colors = [];
	
	/**
	 * copyright message
	 * @var	string
	 */
	public $copyright = '';
	
	/**
	 * list of global variables
	 * @var	array
	 */
	public $globals = [];
	
	/**
	 * image path
	 * @var	string
	 */
	public $imagePath = 'images/';
	
	/**
	 * tainted style
	 * @var	boolean
	 */
	public $isTainted = true;
	
	/**
	 * license name
	 * @var	string
	 */
	public $license = '';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.style.canManageStyle'];
	
	/**
	 * style package name
	 * @var	string
	 */
	public $packageName = '';
	
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
	 * @var	string[]
	 */
	public $variables = [];
	
	/**
	 * list of specialized variables
	 * @var	string[]
	 */
	public $specialVariables = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('styleDescription');
		
		$this->setVariables();
		if (empty($_POST)) {
			$this->readStyleVariables();
		}
		
		$templateGroupList = new TemplateGroupList();
		$templateGroupList->sqlOrderBy = "templateGroupName";
		$templateGroupList->getConditionBuilder()->add('templateGroupFolderName <> ?', ['_wcf_email/']);
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
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		$colors = [];
		foreach ($this->colors as $categoryName => $variables) {
			foreach ($variables as $variable) {
				$colors[] = $categoryName . ucfirst($variable);
			}
		}
		
		// ignore everything except well-formed rgba()
		$regEx = new Regex('rgba\(\d{1,3}, \d{1,3}, \d{1,3}, (1|1\.00?|0|0?\.[0-9]{1,2})\)');
		foreach ($colors as $variableName) {
			if (isset($_POST[$variableName]) && $regEx->match($_POST[$variableName])) {
				$this->variables[$variableName] = $_POST[$variableName];
			}
		}
		
		// read variables with units, e.g. 13px
		foreach ($this->globals as $variableName) {
			if (isset($_POST[$variableName]) && is_numeric($_POST[$variableName])) {
				if (isset($_POST[$variableName.'_unit']) && in_array($_POST[$variableName.'_unit'], $this->availableUnits)) {
					$this->variables[$variableName] = abs($_POST[$variableName]).$_POST[$variableName.'_unit'];
				}
			}
			else {
				// set default value
				$this->variables[$variableName] = '0px';
			}
		}
		
		// read specialized variables
		$integerValues = ['pageLogoHeight', 'pageLogoWidth'];
		foreach ($this->specialVariables as $variableName) {
			if (isset($_POST[$variableName])) {
				$this->variables[$variableName] = (in_array($variableName, $integerValues)) ? abs(intval($_POST[$variableName])) : StringUtil::trim($_POST[$variableName]);
			}
		}
		$this->variables['useFluidLayout'] = isset($_POST['useFluidLayout']) ? 1 : 0;
		$this->variables['useGoogleFont'] = isset($_POST['useGoogleFont']) ? 1 : 0;
		
		// style data
		if (isset($_POST['authorName'])) $this->authorName = StringUtil::trim($_POST['authorName']);
		if (isset($_POST['authorURL'])) $this->authorURL = StringUtil::trim($_POST['authorURL']);
		if (isset($_POST['copyright'])) $this->copyright = StringUtil::trim($_POST['copyright']);
		if (isset($_POST['imagePath'])) $this->imagePath = StringUtil::trim($_POST['imagePath']);
		if (isset($_POST['license'])) $this->license = StringUtil::trim($_POST['license']);
		if (isset($_POST['packageName'])) $this->packageName = StringUtil::trim($_POST['packageName']);
		if (isset($_POST['styleDate'])) $this->styleDate = StringUtil::trim($_POST['styleDate']);
		if (isset($_POST['styleDescription'])) $this->styleDescription = StringUtil::trim($_POST['styleDescription']);
		if (isset($_POST['styleName'])) $this->styleName = StringUtil::trim($_POST['styleName']);
		if (isset($_POST['styleVersion'])) $this->styleVersion = StringUtil::trim($_POST['styleVersion']);
		if (isset($_POST['templateGroupID'])) $this->templateGroupID = intval($_POST['templateGroupID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->authorName)) {
			throw new UserInputException('authorName');
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
				throw new UserInputException('styleDate', 'invalid');
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
			throw new UserInputException('styleVersion', 'invalid');
		}
		
		// validate style package name
		if (!empty($this->packageName)) {
			if (!Package::isValidPackageName($this->packageName)) {
				throw new UserInputException('packageName', 'invalid');
			}
			
			$this->enforcePackageNameRestriction();
		}
		
		// validate template group id
		if ($this->templateGroupID) {
			if (!isset($this->availableTemplateGroups[$this->templateGroupID])) {
				throw new UserInputException('templateGroupID');
			}
		}
		
		// ensure image path is below WCF_DIR/images/
		if ($this->imagePath) {
			$relativePath = FileUtil::unifyDirSeparator(FileUtil::getRelativePath(WCF_DIR.'images/', WCF_DIR.$this->imagePath));
			if (strpos($relativePath, '../') !== false) {
				throw new UserInputException('imagePath', 'invalid');
			}
		}
		
		if (!empty($this->variables['overrideScss'])) {
			$this->parseOverrides();
		}
	}
	
	/**
	 * Disallow the use of `com.woltlab.*` for package names to avoid accidential collisions.
	 * 
	 * @throws      UserInputException
	 */
	protected function enforcePackageNameRestriction() {
		// 3rd party styles may never have com.woltlab.* as name
		if (strpos($this->packageName, 'com.woltlab.') !== false) {
			throw new UserInputException('packageName', 'reserved');
		}
	}
	
	/**
	 * Validates LESS-variable overrides.
	 * 
	 * If an override is invalid, unknown or matches a variable covered by
	 * the style editor itself, it will be silently discarded.
	 * 
	 * @param       string          $variableName
	 * @throws      UserInputException
	 */
	protected function parseOverrides($variableName = 'overrideScss') {
		static $colorNames = null;
		if ($colorNames === null) {
			$colorNames = [];
			foreach ($this->colors as $colorPrefix => $colors) {
				foreach ($colors as $color) {
					$colorNames[] = $colorPrefix . ucfirst($color);
				}
			}
		}
		
		// get available variables
		$sql = "SELECT	variableName
			FROM	wcf".WCF_N."_style_variable";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$variables = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		$lines = explode("\n", StringUtil::unifyNewlines($this->variables[$variableName]));
		$regEx = new Regex('^\$([a-z-A-Z]+):\s*([@a-zA-Z0-9 ,\.\(\)\%\#-]+(\s*!default)?);$');
		$errors = [];
		foreach ($lines as $index => &$line) {
			$line = StringUtil::trim($line);
			
			// ignore empty lines
			if (empty($line)) {
				unset($lines[$index]);
				continue;
			}
			
			if ($regEx->match($line)) {
				$matches = $regEx->getMatches();
				
				// cannot override variables covered by style editor
				if (in_array($matches[1], $colorNames) || in_array($matches[1], $this->globals) || in_array($matches[1], $this->specialVariables)) {
					$errors[] = [
						'error' => 'predefined',
						'text' => $matches[1]
					];
				}
				else if (!in_array($matches[1], $variables)) {
					// unknown style variable
					$errors[] = [
						'error' => 'unknown',
						'text' => $matches[1]
					];
				}
				else {
					$this->variables[$matches[1]] = $matches[2];
				}
			}
			else {
				// not valid
				$errors[] = [
					'error' => 'invalid',
					'text' => $line
				];
			}
		}
		
		$this->variables[$variableName] = implode("\n", $lines);
		
		if (!empty($errors)) {
			throw new UserInputException($variableName, $errors);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// parse global (unit) variables
		foreach ($this->globals as $variableName) {
			if (preg_match('/(.*?)(' . implode('|', $this->availableUnits) . ')$/', $this->variables[$variableName], $match)) {
				$this->variables[$variableName] = $match[1];
				$this->variables[$variableName.'_unit'] = $match[2];
			}
		}
		
		if (empty($_POST)) {
			$this->setDefaultValues();
		}
	}
	
	/**
	 * Sets available variables
	 */
	protected function setVariables() {
		$this->colorCategories = [
			'wcfHeader' => ['wcfHeader', 'wcfHeaderSearchBox', 'wcfHeaderMenu', 'wcfHeaderMenuDropdown'],
			'wcfNavigation' => 'wcfNavigation',
			'wcfSidebar' => ['wcfSidebar', 'wcfSidebarDimmed', 'wcfSidebarHeadline'],
			'wcfContent' => ['wcfContent', 'wcfContentDimmed', 'wcfContentHeadline'],
			'wcfTabularBox' => 'wcfTabularBox',
			'wcfInput' => ['wcfInput', 'wcfInputDisabled'],
			'wcfButton' => ['wcfButton', 'wcfButtonPrimary', 'wcfButtonDisabled'],
			'wcfDropdown' => 'wcfDropdown',
			'wcfStatus' => ['wcfStatusInfo', 'wcfStatusSuccess', 'wcfStatusWarning', 'wcfStatusError'],
			'wcfFooterBox' => ['wcfFooterBox', 'wcfFooterBoxHeadline'],
			'wcfFooter' => ['wcfFooter', 'wcfFooterHeadline', 'wcfFooterCopyright']
		];
		
		$this->colors = [
			'wcfHeader' => ['background', 'text', 'link', 'linkActive'],
			'wcfHeaderSearchBox' => ['background', 'text', 'placeholder', 'placeholderActive', 'backgroundActive', 'textActive'],
			'wcfHeaderMenu' => ['background', 'linkBackground', 'linkBackgroundActive', 'link', 'linkActive'],
			'wcfHeaderMenuDropdown' => ['background', 'link', 'backgroundActive', 'linkActive'],
			'wcfNavigation' => ['background', 'text', 'link', 'linkActive'],
			'wcfSidebar' => ['background', 'text', 'link', 'linkActive'],
			'wcfSidebarDimmed' => ['text', 'link', 'linkActive'],
			'wcfSidebarHeadline' => ['text', 'link', 'linkActive'],
			'wcfContent' => ['background', 'border', 'borderInner', 'text', 'link', 'linkActive'],
			'wcfContentDimmed' => ['text', 'link', 'linkActive'],
			'wcfContentHeadline' => ['border', 'text', 'link', 'linkActive'],
			'wcfTabularBox' => ['borderInner', 'headline', 'backgroundActive', 'headlineActive'],
			'wcfInput' => ['label', 'background', 'border', 'text', 'placeholder', 'placeholderActive', 'backgroundActive', 'borderActive', 'textActive'],
			'wcfInputDisabled' => ['background', 'border', 'text'],
			'wcfButton' => ['background', 'text', 'backgroundActive', 'textActive'],
			'wcfButtonPrimary' => ['background', 'text', 'backgroundActive', 'textActive'],
			'wcfButtonDisabled' => ['background', 'text'],
			'wcfDropdown' => ['background', 'borderInner', 'text', 'link', 'backgroundActive', 'linkActive'],
			'wcfStatusInfo' => ['background', 'border', 'text', 'link', 'linkActive'],
			'wcfStatusSuccess' => ['background', 'border', 'text', 'link', 'linkActive'],
			'wcfStatusWarning' => ['background', 'border', 'text', 'link', 'linkActive'],
			'wcfStatusError' => ['background', 'border', 'text', 'link', 'linkActive'],
			'wcfFooterBox' => ['background', 'text', 'link', 'linkActive'],
			'wcfFooterBoxHeadline' => ['text', 'link', 'linkActive'],
			'wcfFooter' => ['background', 'text', 'link', 'linkActive'],
			'wcfFooterHeadline' => ['text', 'link', 'linkActive'],
			'wcfFooterCopyright' => ['background', 'text', 'link', 'linkActive']
		];
		
		// set global variables
		$this->globals = [
			'wcfFontSizeSmall',
			'wcfFontSizeDefault',
			'wcfFontSizeHeadline',
			'wcfFontSizeSection',
			'wcfFontSizeTitle',
			
			'wcfLayoutFixedWidth',
			'wcfLayoutMinWidth',
			'wcfLayoutMaxWidth'
		];
		
		// set specialized variables
		$this->specialVariables = [
			'individualScss',
			'overrideScss',
			'pageLogo',
			'pageLogoWidth',
			'pageLogoHeight',
			'pageLogoMobile',
			'useFluidLayout',
			'useGoogleFont',
			'wcfFontFamilyGoogle',
			'wcfFontFamilyFallback'
		];
		
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
		$this->variables = $statement->fetchMap('variableName', 'defaultValue');
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new StyleAction([], 'create', [
			'data' => array_merge($this->additionalFields, [
				'styleName' => $this->styleName,
				'templateGroupID' => $this->templateGroupID,
				'packageName' => $this->packageName,
				'isDisabled' => 1, // styles are disabled by default
				'isTainted' => 1,
				'styleDescription' => '',
				'styleVersion' => $this->styleVersion,
				'styleDate' => $this->styleDate,
				'imagePath' => $this->imagePath,
				'copyright' => $this->copyright,
				'license' => $this->license,
				'authorName' => $this->authorName,
				'authorURL' => $this->authorURL
			]),
			'tmpHash' => $this->tmpHash,
			'variables' => $this->variables
		]);
		$returnValues = $this->objectAction->executeAction();
		$style = $returnValues['returnValues'];
		
		// save style description
		I18nHandler::getInstance()->save('styleDescription', 'wcf.style.styleDescription'.$style->styleID, 'wcf.style');
		
		$styleEditor = new StyleEditor($style);
		$styleEditor->update([
			'styleDescription' => 'wcf.style.styleDescription'.$style->styleID
		]);
		
		// call saved event
		$this->saved();
		
		// reset variables
		$this->authorName = $this->authorURL = $this->copyright = $this->packageName = '';
		$this->license = $this->styleDate = $this->styleDescription = $this->styleName = $this->styleVersion = '';
		$this->setDefaultValues();
		$this->imagePath = 'images/';
		$this->isTainted = true;
		$this->templateGroupID = 0;
		
		I18nHandler::getInstance()->reset();
		
		// reload variables
		$this->readStyleVariables();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'add',
			'authorName' => $this->authorName,
			'authorURL' => $this->authorURL,
			'availableFontFamilies' => $this->availableFontFamilies,
			'availableTemplateGroups' => $this->availableTemplateGroups,
			'availableUnits' => $this->availableUnits,
			'colorCategories' => $this->colorCategories,
			'colors' => $this->colors,
			'copyright' => $this->copyright,
			'imagePath' => $this->imagePath,
			'isTainted' => $this->isTainted,
			'license' => $this->license,
			'packageName' => $this->packageName,
			'styleDate' => $this->styleDate,
			'styleDescription' => $this->styleDescription,
			'styleName' => $this->styleName,
			'styleVersion' => $this->styleVersion,
			'templateGroupID' => $this->templateGroupID,
			'tmpHash' => $this->tmpHash,
			'variables' => $this->variables
		]);
	}
	
	protected function setDefaultValues() {
		$this->authorName = WCF::getUser()->username;
		$this->styleDate = gmdate('Y-m-d', TIME_NOW);
		$this->styleVersion = '1.0.0';
	}
}
