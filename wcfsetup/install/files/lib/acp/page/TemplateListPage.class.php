<?php
namespace wcf\acp\page;
use wcf\data\template\group\TemplateGroupList;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of templates.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class TemplateListPage extends SortablePage {
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.template.list';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.template.canManageTemplate');
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\template\TemplateList';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = 100;
	
	/**
	 * @see	wcf\page\SortablePage::$itemsPerPage
	 */
	public $defaultSortField = 'templateName';
	
	/**
	 * @see	wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('templateID', 'templateName', 'lastModificationTime');
	
	/**
	 * template group id
	 * @var integer
	 */
	public $templateGroupID = 0;
	
	/**
	 * template name
	 * @var string
	 */
	public $searchTemplateName = '';
	
	/**
	 * available template groups
	 * @var array
	 */
	public $availableTemplateGroups = array();
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
	
		if (isset($_REQUEST['templateGroupID'])) $this->templateGroupID = intval($_REQUEST['templateGroupID']);
		if (isset($_REQUEST['searchTemplateName'])) $this->searchTemplateName = StringUtil::trim($_REQUEST['searchTemplateName']);
	}
	
	/**
	 * @see	wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if ($this->templateGroupID) $this->objectList->getConditionBuilder()->add('template.templateGroupID = ?', array($this->templateGroupID));
		else $this->objectList->getConditionBuilder()->add('template.templateGroupID IS NULL');
		
		if ($this->searchTemplateName) $this->objectList->getConditionBuilder()->add('templateName LIKE ?', array($this->searchTemplateName.'%'));
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get template groups		
		$templateGroupList = new TemplateGroupList();
		$templateGroupList->readObjects();
		$this->availableTemplateGroups = $templateGroupList->getObjects();
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
	
		WCF::getTPL()->assign(array(
			'templateGroupID' => $this->templateGroupID,
			'searchTemplateName' => $this->searchTemplateName,
			'availableTemplateGroups' => $this->availableTemplateGroups
		));
	}
}
