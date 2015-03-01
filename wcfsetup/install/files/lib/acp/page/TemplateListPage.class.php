<?php
namespace wcf\acp\page;
use wcf\data\package\PackageCache;
use wcf\data\template\group\TemplateGroup;
use wcf\page\SortablePage;
use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of templates.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class TemplateListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.template.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.template.canManageTemplate');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\template\TemplateList';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = 100;
	
	/**
	 * @see	\wcf\page\SortablePage::$itemsPerPage
	 */
	public $defaultSortField = 'templateName';
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('templateID', 'templateName', 'lastModificationTime');
	
	/**
	 * template group id
	 * @var	integer
	 */
	public $templateGroupID = 0;
	
	/**
	 * template name
	 * @var	string
	 */
	public $searchTemplateName = '';
	
	/**
	 * application
	 * @var	string
	 */
	public $application = '';
	
	/**
	 * available template groups
	 * @var	array
	 */
	public $availableTemplateGroups = array();
	
	/**
	 * available applications
	 * @var	array
	 */
	public $availableApplications = array();
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['templateGroupID'])) $this->templateGroupID = intval($_REQUEST['templateGroupID']);
		if (isset($_REQUEST['searchTemplateName'])) $this->searchTemplateName = StringUtil::trim($_REQUEST['searchTemplateName']);
		if (isset($_REQUEST['application'])) $this->application = StringUtil::trim($_REQUEST['application']);
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if ($this->templateGroupID) $this->objectList->getConditionBuilder()->add('template.templateGroupID = ?', array($this->templateGroupID));
		else $this->objectList->getConditionBuilder()->add('template.templateGroupID IS NULL');
		
		if ($this->searchTemplateName) $this->objectList->getConditionBuilder()->add('templateName LIKE ?', array('%'.$this->searchTemplateName.'%'));
		if ($this->application) $this->objectList->getConditionBuilder()->add('application = ?', array($this->application));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get template groups
		$this->availableTemplateGroups = TemplateGroup::getSelectList(array(), 1);
		
		// get applications
		$applications = ApplicationHandler::getInstance()->getApplications();
		$applications[] = ApplicationHandler::getInstance()->getWCF();
		foreach ($applications as $application) {
			$package = PackageCache::getInstance()->getPackage($application->packageID);
			$this->availableApplications[ApplicationHandler::getInstance()->getAbbreviation($package->packageID)] = $package;
			
			// issues with the language cache would cause the uasort() below to throw a
			// misleading error message, calling it here just reveals the real error
			$package->getName();
		}
		
		uasort($this->availableApplications, function ($a, $b) {
			return $a->getName() > $b->getName();
		});
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'templateGroupID' => $this->templateGroupID,
			'searchTemplateName' => $this->searchTemplateName,
			'application' => $this->application,
			'availableTemplateGroups' => $this->availableTemplateGroups,
			'availableApplications' => $this->availableApplications
		));
	}
}
