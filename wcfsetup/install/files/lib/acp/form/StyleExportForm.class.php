<?php
namespace wcf\acp\form;
use wcf\data\package\Package;
use wcf\data\style\Style;
use wcf\data\style\StyleEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the style export form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class StyleExportForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.style';
	
	/**
	 * true, if style has custom images
	 * @var	boolean
	 */
	public $canExportImages = false;
	
	/**
	 * true, if style has custom templates
	 * @var	boolean
	 */
	public $canExportTemplates = false;
	
	/**
	 * export style as installable package
	 * @var	boolean
	 */
	public $exportAsPackage = false;
	
	/**
	 * true, if images should be exported
	 * @var	boolean
	 */
	public $exportImages = false;
	
	/**
	 * true, if templates should be exported
	 * @var	boolean
	 */
	public $exportTemplates = false;
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.style.canManageStyle');
	
	/**
	 * package identifier
	 * @var	string
	 */
	public $packageName = '';
	
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
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->styleID = intval($_REQUEST['id']);
		$this->style = new Style($this->styleID);
		if (!$this->style->styleID) {
			throw new IllegalLinkException();
		}
		
		if ($this->style->imagePath && $this->style->imagePath != 'images/') $this->canExportImages = true;
		if ($this->style->templateGroupID) $this->canExportTemplates = true;
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if ($this->canExportImages && isset($_POST['exportImages'])) $this->exportImages = true;
		if ($this->canExportTemplates && isset($_POST['exportTemplates'])) $this->exportTemplates = true;
		
		if (isset($_POST['exportAsPackage'])) {
			$this->exportAsPackage = true;
			
			if (isset($_POST['packageName'])) $this->packageName = StringUtil::trim($_POST['packageName']);
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->exportAsPackage) {
			if (empty($this->packageName)) {
				throw new UserInputException('packageName');
			}
			
			if (!Package::isValidPackageName($this->packageName)) {
				throw new UserInputException('packageName', 'notValid');
			}
			
			// 3rd party packages may never have com.woltlab.* as name
			if (strpos($this->packageName, 'com.woltlab.') === 0) {
				throw new UserInputException('packageName', 'reserved');
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// get style filename
		$filename = str_replace(' ', '-', preg_replace('/[^a-z0-9 _-]/', '', mb_strtolower($this->style->styleName)));
		
		// send headers
		header('Content-Type: application/x-gzip; charset=utf-8');
		
		if ($this->exportAsPackage) {
			header('Content-Disposition: attachment; filename="'.$this->packageName.'.tar.gz"');
		}
		else {
			header('Content-Disposition: attachment; filename="'.$filename.'-style.tgz"');
		}
		
		// export style
		$styleEditor = new StyleEditor($this->style);
		$styleEditor->export($this->exportTemplates, $this->exportImages, $this->packageName);
		
		// call saved event
		$this->saved();
		
		exit;
	}
	
	/**
	 * @see	\wcf\form\IForm::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'canExportImages' => $this->canExportImages,
			'canExportTemplates' => $this->canExportTemplates,
			'exportAsPackage' => $this->exportAsPackage,
			'exportImages' => $this->exportImages,
			'exportTemplates' => $this->exportTemplates,
			'packageName' => $this->packageName,
			'style' => $this->style,
			'styleID' => $this->styleID
		));
	}
}
