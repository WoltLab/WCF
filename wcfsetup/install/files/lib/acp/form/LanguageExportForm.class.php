<?php
namespace wcf\acp\form;
use wcf\data\language\LanguageEditor;
use wcf\data\package\Package;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Shows the language export form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class LanguageExportForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.language.canManageLanguage'];
	
	/**
	 * language id
	 * @var	integer
	 */
	public $languageID = 0;
	
	/**
	 * language editor object
	 * @var	\wcf\data\language\LanguageEditor
	 */
	public $language = null;
	
	/**
	 * selected packages
	 * @var	string[]
	 */
	public $selectedPackages = [];
	
	/**
	 * available packages
	 * @var	string[]
	 */
	public $packages = [];
	
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
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->languageID = intval($_REQUEST['id']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['selectedPackages']) && is_array($_POST['selectedPackages'])) {
			$selectedPackages = ArrayUtil::toIntegerArray($_POST['selectedPackages']);
			$this->selectedPackages = array_combine($selectedPackages, $selectedPackages);
			if (isset($this->selectedPackages[0])) unset($this->selectedPackages[0]);
		}
		
		if (isset($_POST['exportCustomValues'])) $this->exportCustomValues = intval($_POST['exportCustomValues']);
		if (isset($_POST['languageID'])) $this->languageID = intval($_POST['languageID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		$language = LanguageFactory::getInstance()->getLanguage($this->languageID);
		if ($language === null) {
			throw new UserInputException('languageID', 'noValidSelection');
		}
		$this->language = new LanguageEditor($language);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST) && $this->languageID) {
			$language = LanguageFactory::getInstance()->getLanguage($this->languageID);
			if ($language === null) {
				throw new IllegalLinkException();
			}
			$this->language = new LanguageEditor($language);
		}
		
		$this->readPackages();
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'languageID' => $this->languageID,
			'languages' => LanguageFactory::getInstance()->getLanguages(),
			'selectedPackages' => $this->selectedPackages,
			'packages' => $this->packages,
			'selectAllPackages' => true,
			'packageNameLength' => $this->packageNameLength
		]);
	}
	
	/**
	 * Read available packages.
	 */
	protected function readPackages() {
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package
			ORDER BY	packageName";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$row['packageNameLength'] = mb_strlen(WCF::getLanguage()->get($row['packageName']));
			$this->packages[] = new Package(null, $row);
			if ($row['packageNameLength'] > $this->packageNameLength) {
				$this->packageNameLength = $row['packageNameLength'];
			}
		}
	}
}
