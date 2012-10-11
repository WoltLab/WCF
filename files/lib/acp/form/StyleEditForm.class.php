<?php
namespace wcf\acp\form;
use wcf\data\style\Style;
use wcf\data\style\StyleAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the style edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.acp.style
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class StyleEditForm extends StyleAddForm {
	/**
	 * @see	wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.style';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.style.canEditStyle');
	
	/**
	 * style object
	 * @var	wcf\data\style\Style
	 */
	public $style = null;
	
	/**
	 * style id
	 * @var	integer
	 */
	public $styleID = 0;
	
	/**
	 * @see	wcf\page\IPage::readParameters()
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
	 * @see	wcf\acp\form\StyleAddForm::readStyleVariables()
	 */
	protected function readStyleVariables() {
		$this->variables = $this->style->getVariables();
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->authorName = $this->style->authorName;
			$this->authorURL = $this->style->authorURL;
			$this->copyright = $this->style->copyright;
			$this->iconPath = $this->style->iconPath;
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
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::saved();
		
		$this->objectAction = new StyleAction(array($this->style), 'update', array(
			'data' => array(
				'styleName' => $this->styleName,
				'templateGroupID' => $this->templateGroupID,
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
		
		// reload style object to update preview image
		$this->style = new Style($this->style->styleID);
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'style' => $this->style,
			'styleID' => $this->styleID
		));
	}
}
