<?php
namespace wcf\acp\page;
use wcf\system\menu\acp\ACPMenu;
use wcf\data\package\Package;
use wcf\data\search\Search;
use wcf\page\SortablePage;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the list of package update search results.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class PackageUpdateSearchResultPage extends SortablePage {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'packageUpdateSearchResult';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canInstallPackage');
	
	/**
	 * @see wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'packageName';
	
	/**
	 * @see wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('package', 'packageName', 'author');
	
	/**
	 * id of the package update search
	 * @var	integer
	 */
	public $searchID = 0;
	
	/**
	 * search object
	 * @var	wcf\data\search\Search
	 */
	public $search = null;

	/**
	 * list with data of package updates
	 * @var	array
	 */
	public $packages = array();
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['searchID'])) $this->searchID = intval($_REQUEST['searchID']);
		
		// get search data
		$conditions = new PrepareStatementConditionBuilder();
		$conditions->add("searchID = ?", array($this->searchID));
		$conditions->add("userID = ?", array(WCF::getUser()->userID));
		$conditions->add("searchType = ?", array('packages'));
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_search
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$this->search = new Search(null, $statement->fetchArray());
		if (empty($this->search->searchID)) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read packages
		$this->readPackages();
	}
	
	/**
	 * @see wcf\page\MultipleLinkPage::countItems()
	 */
	public function countItems() {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("packageUpdateID IN (?)", array(explode(',', $this->search->searchData)));
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package_update
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$row = $statement->fetchArray();
		
		return $row['count'];
	}
	
	/**
	 * Gets a list of packages.
	 */
	protected function readPackages() {
		if ($this->items) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("packageUpdateID IN (?)", array(explode(',', $this->search->searchData)));
			
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_package_update
				".$conditions."
				ORDER BY	".$this->sortField." ".$this->sortOrder;
			$statement = WCF::getDB()->prepareStatement($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				// default values
				$row['isUnique'] = 0;
				$row['updatableInstances'] = array();
				$row['packageVersions'] = array();
				$row['packageVersion'] = '1.0.0';
				$row['instances'] = 0;
				
				// get package versions
				$sql = "SELECT	packageVersion
					FROM	wcf".WCF_N."_package_update_version
					WHERE	packageUpdateID IN (
							SELECT	packageUpdateID
							FROM	wcf".WCF_N."_package_update
							WHERE	package = ?
						)";
				$statement2 = WCF::getDB()->prepareStatement($sql);
				$statement2->execute(array($row['package']));
				while ($row2 = $statement2->fetchArray()) {
					$row['packageVersions'][] = $row2['packageVersion'];
				}
				
				if (count($row['packageVersions'])) {
					// remove duplicates
					$row['packageVersions'] = array_unique($row['packageVersions']);
					// sort versions
					usort($row['packageVersions'], array('wcf\data\package\Package', 'compareVersion'));
					// take lastest version
					$row['packageVersion'] = end($row['packageVersions']);
				}
					
				// get installed instances
				$sql = "SELECT	package.*, CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
					FROM	wcf".WCF_N."_package package
					WHERE 	package.package = ?";
				$statement2 = WCF::getDB()->prepareStatement($sql);
				$statement2->execute(array($row['package']));
				while ($row2 = $statement2->fetchArray()) {
					$row['instances']++;

					// is already installed unique?
					if ($row2['isUnique'] == 1) $row['isUnique'] = 1;
					
					// check update support
					if (Package::compareVersion($row2['packageVersion'], $row['packageVersion'], '<')) {
						$row['updatableInstances'][] = $row2;
					}
				}
				
				$this->packages[] = $row;
			}
		}
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'searchID' => $this->searchID,
			'packages' => $this->packages,
			'selectedPackages' => array()
		));
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// set active menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.package.database');
		
		parent::show();
	}
}
