<?php
namespace wcf\acp\form;
use wcf\data\language\LanguageEditor;
use wcf\data\package\Package;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the language export form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class LanguageExportForm extends AbstractForm {
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.language.canManageLanguage');
	
	/**
	 * language id
	 * @var	integer
	 */
	public $languageID = 0;
	
	/**
	 * language editor object
	 * @var	wcf\data\language\LanguageEditor
	 */
	public $language = null;
	
	/**
	 * selected packages
	 * @var	array<string>
	 */
	public $selectedPackages = array();
	
	/**
	 * available packages
	 * @var	array<string>
	 */
	public $packages = array();
	
	/**
	 * true to export custom variables
	 * @var	boolean
	 */
	public $exportCustomValues = false;
	
	/**
	 * max package name length
	 * @var	integer
	 */
	public $packageNameLength = 0; 
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get language editor object
		if (isset($_REQUEST['id'])) $this->languageID = intval($_REQUEST['id']);
		$language = LanguageFactory::getInstance()->getLanguage($this->languageID);
		if ($language === null) {
			throw new IllegalLinkException();
		}
		$this->language = new LanguageEditor($language);
	}
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['selectedPackages']) && is_array($_POST['selectedPackages'])) {
			$selectedPackages = ArrayUtil::toIntegerArray($_POST['selectedPackages']);
			$this->selectedPackages = array_combine($selectedPackages, $selectedPackages);
			if (isset($this->selectedPackages[0])) unset($this->selectedPackages[0]);
		}
		
		if (isset($_POST['exportCustomValues'])) {
			$this->exportCustomValues = intval($_POST['exportCustomValues']);
		}
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readPackages();
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// send headers
		header('Content-Type: text/xml; charset=UTF-8');
		header('Content-Disposition: attachment; filename="'.$this->language->languageCode.'.xml"');
		$this->language->export($this->selectedPackages, $this->exportCustomValues);
		exit;
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'languageID' => $this->languageID,
			'languages' => LanguageFactory::getInstance()->getLanguages(),
			'selectedPackages' => $this->selectedPackages,
			'packages' => $this->packages,
			'selectAllPackages' => true,
			'packageNameLength' => $this->packageNameLength
		));
	}
	
	/**
	 * Read available packages.
	 */
	protected function readPackages() {
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package
			ORDER BY	packageName";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->languageID));
		while ($row = $statement->fetchArray()) {
			$row['packageNameLength'] = mb_strlen(WCF::getLanguage()->get($row['packageName']));
			$this->packages[] = new Package(null, $row);
			if ($row['packageNameLength'] > $this->packageNameLength) {
				$this->packageNameLength = $row['packageNameLength'];	
			} 
		}
	}
}
