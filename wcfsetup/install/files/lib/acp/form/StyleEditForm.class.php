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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class StyleEditForm extends StyleAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
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
	 * @see	\wcf\page\IPage::readParameters()
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
	 * @see	\wcf\acp\form\StyleAddForm::readStyleVariables()
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
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		I18nHandler::getInstance()->setOptions('styleDescription', PACKAGE_ID, $this->style->styleDescription, 'wcf.style.styleDescription\d+');
		
		if (empty($_POST)) {
			$this->authorName = $this->style->authorName;
			$this->authorURL = $this->style->authorURL;
			$this->copyright = $this->style->copyright;
			$this->imagePath = $this->style->imagePath;
			$this->license = $this->style->license;
			$this->styleDate = $this->style->styleDate;
			$this->styleDescription = $this->style->styleDescription;
			$this->styleName = $this->style->styleName;
			$this->styleVersion = $this->style->styleVersion;
			$this->templateGroupID = $this->style->templateGroupID;
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		$this->objectAction = new StyleAction(array($this->style), 'update', array(
			'data' => array_merge($this->additionalFields, array(
				'styleName' => $this->styleName,
				'templateGroupID' => $this->templateGroupID,
				'styleVersion' => $this->styleVersion,
				'styleDate' => $this->styleDate,
				'imagePath' => $this->imagePath,
				'copyright' => $this->copyright,
				'license' => $this->license,
				'authorName' => $this->authorName,
				'authorURL' => $this->authorURL
			)),
			'tmpHash' => $this->tmpHash,
			'variables' => $this->variables
		));
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
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'style' => $this->style,
			'styleID' => $this->styleID
		));
	}
}
