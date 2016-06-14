<?php
namespace wcf\acp\form;
use wcf\data\style\Style;
use wcf\data\style\StyleAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the style edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class StyleEditForm extends StyleAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.style';
	
	/**
	 * style object
	 * @var	\wcf\data\style\Style
	 */
	public $style = null;
	
	/**
	 * style id
	 * @var	integer
	 */
	public $styleID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->styleID = intval($_REQUEST['id']);
		$this->style = new Style($this->styleID);
		if (!$this->style->styleID) {
			throw new IllegalLinkException();
		}
		
		parent::readParameters();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readStyleVariables() {
		$this->variables = $this->style->getVariables();
		
		// fix empty values ~""
		foreach ($this->variables as &$variableValue) {
			if ($variableValue == '~""') {
				$variableValue = '';
			}
		}
		unset($variableValue);
		
		if (!$this->style->isTainted) {
			$tmp = Style::splitLessVariables($this->variables['individualScss']);
			$this->variables['individualScss'] = $tmp['preset'];
			$this->variables['individualScssCustom'] = $tmp['custom'];
			
			$tmp = Style::splitLessVariables($this->variables['overrideScss']);
			$this->variables['overrideScss'] = $tmp['preset'];
			$this->variables['overrideScssCustom'] = $tmp['custom'];
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function setVariables() {
		parent::setVariables();
		
		if (!$this->style->isTainted) {
			$this->specialVariables[] = 'individualScssCustom';
			$this->specialVariables[] = 'overrideScssCustom';
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		I18nHandler::getInstance()->setOptions('styleDescription', PACKAGE_ID, $this->style->styleDescription, 'wcf.style.styleDescription\d+');
		
		if (empty($_POST)) {
			$this->authorName = $this->style->authorName;
			$this->authorURL = $this->style->authorURL;
			$this->copyright = $this->style->copyright;
			$this->imagePath = $this->style->imagePath;
			$this->isTainted = $this->style->isTainted;
			$this->license = $this->style->license;
			$this->packageName = $this->style->packageName;
			$this->styleDate = $this->style->styleDate;
			$this->styleDescription = $this->style->styleDescription;
			$this->styleName = $this->style->styleName;
			$this->styleVersion = $this->style->styleVersion;
			$this->templateGroupID = $this->style->templateGroupID;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// TODO: how should this actually work?
		/*if (!$this->style->isTainted) {
			$this->variables['individualScss'] = Style::joinLessVariables($this->variables['individualScss'], $this->variables['individualScssCustom']);
			$this->variables['overrideScss'] = Style::joinLessVariables($this->variables['overrideScss'], $this->variables['overrideScssCustom']);
			
			unset($this->variables['individualScssCustom']);
			unset($this->variables['overrideScssCustom']);
		}*/
		
		$this->objectAction = new StyleAction([$this->style], 'update', [
			'data' => array_merge($this->additionalFields, [
				'styleName' => $this->styleName,
				'templateGroupID' => $this->templateGroupID,
				'styleVersion' => $this->styleVersion,
				'styleDate' => $this->styleDate,
				'imagePath' => $this->imagePath,
				'copyright' => $this->copyright,
				'packageName' => $this->packageName,
				'license' => $this->license,
				'authorName' => $this->authorName,
				'authorURL' => $this->authorURL
			]),
			'tmpHash' => $this->tmpHash,
			'variables' => $this->variables
		]);
		$this->objectAction->executeAction();
		
		// save description
		I18nHandler::getInstance()->save('styleDescription', $this->style->styleDescription, 'wcf.style');
		
		// call saved event
		$this->saved();
		
		// reload style object to update preview image
		$this->style = new Style($this->style->styleID);
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'style' => $this->style,
			'styleID' => $this->styleID
		]);
	}
}
