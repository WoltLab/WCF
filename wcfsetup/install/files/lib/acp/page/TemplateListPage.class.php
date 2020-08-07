<?php
namespace wcf\acp\page;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\TemplateList;
use wcf\page\SortablePage;
use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of templates.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	TemplateList	$objectList
 */
class TemplateListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.template.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.template.canManageTemplate'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = TemplateList::class;
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 100;
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'templateName';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['templateID', 'templateName', 'lastModificationTime'];
	
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
	public $availableTemplateGroups = [];
	
	/**
	 * available applications
	 * @var	array
	 */
	public $availableApplications = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['templateGroupID'])) $this->templateGroupID = intval($_REQUEST['templateGroupID']);
		if (isset($_REQUEST['searchTemplateName'])) $this->searchTemplateName = StringUtil::trim($_REQUEST['searchTemplateName']);
		if (isset($_REQUEST['application'])) $this->application = StringUtil::trim($_REQUEST['application']);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if ($this->templateGroupID) $this->objectList->getConditionBuilder()->add('template.templateGroupID = ?', [$this->templateGroupID]);
		else $this->objectList->getConditionBuilder()->add('template.templateGroupID IS NULL');
		
		if ($this->searchTemplateName) $this->objectList->getConditionBuilder()->add('templateName LIKE ?', ['%'.$this->searchTemplateName.'%']);
		if ($this->application) $this->objectList->getConditionBuilder()->add('application = ?', [$this->application]);
		
		$this->objectList->getConditionBuilder()->add('templateName <> ?', ['.htac']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// get template groups
		$this->availableTemplateGroups = TemplateGroup::getSelectList([], 1);
		
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
		
		uasort($this->availableApplications, function (Package $a, Package $b) {
			return $a->getName() <=> $b->getName();
		});
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'templateGroupID' => $this->templateGroupID,
			'searchTemplateName' => $this->searchTemplateName,
			'application' => $this->application,
			'availableTemplateGroups' => $this->availableTemplateGroups,
			'availableApplications' => $this->availableApplications
		]);
	}
}
