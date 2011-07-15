<?php
namespace wcf\acp\form;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\search\SearchEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the package update search form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class PackageUpdateSearchForm extends ACPForm {
	public $templateName = 'packageUpdateSearch';
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canInstallPackage');
	public $activeMenuItem = 'wcf.acp.menu.link.package.database';
	
	public $packageUpdateServerIDs = array();
	public $packageName = '';
	public $author = '';
	public $searchDescription = 0;
	public $plugin = 1;
	public $standalone = 1;
	public $other = 0;
	public $ignoreUniques = 1;
	
	public $updateServers = array();
	public $packageUpdateIDs = '';
	
	/**
	 * @see wcf\form\Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->ignoreUniques = $this->plugin = $this->standalone = 0;
		if (isset($_POST['packageUpdateServerIDs']) && is_array($_POST['packageUpdateServerIDs'])) $this->packageUpdateServerIDs = ArrayUtil::toIntegerArray($_POST['packageUpdateServerIDs']);
		if (isset($_POST['packageName'])) $this->packageName = StringUtil::trim($_POST['packageName']);
		if (isset($_POST['author'])) $this->author = StringUtil::trim($_POST['author']);
		if (isset($_POST['searchDescription'])) $this->searchDescription = intval($_POST['searchDescription']);
		if (isset($_POST['plugin'])) $this->plugin = intval($_POST['plugin']);
		if (isset($_POST['standalone'])) $this->standalone = intval($_POST['standalone']);
		if (isset($_POST['other'])) $this->other = intval($_POST['other']);
		if (isset($_POST['ignoreUniques'])) $this->ignoreUniques = intval($_POST['ignoreUniques']);
	}
	
	/**
	 * @see wcf\form\Form::validate()
	 */
	public function validate() {
		parent::validate();

		// refresh package database
		PackageUpdateDispatcher::refreshPackageDatabase($this->packageUpdateServerIDs);
		
		// build conditions
		$conditions = new PreparedStatementConditionBuilder();
		// update servers
		if (count($this->packageUpdateServerIDs)) $conditions->add("packageUpdateServerID IN (?)", array($this->packageUpdateServerIDs));
		// name
		if (!empty($this->packageName)) {
			$condition = "packageName LIKE ?";
			$parameters = array('%'.$this->packageName.'%');
			
			if ($this->searchDescription) {
				$condition .= " OR packageDescription LIKE ?";
				$parameters[] = '%'.$this->packageName.'%';
			}
			
			$conditions->add('('.$condition.')', $parameters);
		}
		// author
		if (!empty($this->author)) $conditions->add("author LIKE ?", array($this->author));
		// ignore already installed uniques
		if ($this->ignoreUniques == 1) $conditions->add("package NOT IN (SELECT package FROM wcf".WCF_N."_package WHERE isUnique = 1)");
		// package type
		if (($this->plugin == 0 || $this->standalone == 0 || $this->other == 0) && ($this->plugin == 1 || $this->standalone == 1 || $this->other == 1)) {
			if ($this->standalone == 1) {
				$condition = 'standalone = 1';
				if ($this->plugin == 1) {
					$condition .= " OR plugin IN (SELECT package FROM wcf".WCF_N."_package)";
				}
				else if ($this->other == 1) { 
					$condition .= " OR plugin = ''";
				}
				
				$conditions->add('('.$condition.')');
			}
			else if ($this->plugin == 1) {
				$condition = "plugin IN (SELECT package FROM wcf".WCF_N."_package)";
				if ($this->other == 1) { 
					$condition .= " OR standalone = 0";
				}
				
				$conditions->add('('.$condition.')');
			}
			else if ($this->other) {
				$conditions->add("(standalone = 0 AND plugin = '')");
			}
		}
		
		// search package database
		$packages = array();
		$packageUpdateIDs = array();
		$sql = "SELECT	package, packageUpdateID
			FROM	wcf".WCF_N."_package_update
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql, 1000);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			$packageUpdateIDs[] = $row['packageUpdateID'];
			
			if (!isset($packages[$row['package']])) $packages[$row['package']] = array();
			$packages[$row['package']][$row['packageUpdateID']] = array();
		}
		
		if (empty($packageUpdateIDs)) {
			throw new UserInputException('packageName');
		}
		
		// remove duplicates
		$condition = '';
		$statementParameters = array();
		foreach ($packageUpdateIDs as $packageUpdateID) {
			if (!empty($condition)) $condition .= ',';
			$condition .= '?';
			$statementParameters[] = $packageUpdateID;
		}
		
		$sql = "SELECT		puv.packageVersion, pu.package, pu.packageUpdateID
			FROM		wcf".WCF_N."_package_update_version puv
			LEFT JOIN	wcf".WCF_N."_package_update pu
			ON		(pu.packageUpdateID = puv.packageUpdateID)
			WHERE		puv.packageUpdateID IN (".$condition.")";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($statementParameters);
		while ($row = $statement->fetchArray()) {
			$packages[$row['package']][$row['packageUpdateID']][] = $row['packageVersion'];
		}
		
		foreach ($packages as $identifier => $packageUpdates) {
			if (count($packageUpdates) > 1) {
				foreach ($packageUpdates as $packageUpdateID => $versions) {
					usort($versions, array('wcf\data\package\Package', 'compareVersion'));
					$packageUpdates[$packageUpdateID] = array_pop($versions);
				}
				
				uasort($packageUpdates, array('wcf\data\package\Package', 'compareVersion'));
			}
			
			$keys = array_keys($packageUpdates);
			if (!empty($this->packageUpdateIDs)) $this->packageUpdateIDs .= ',';
			$this->packageUpdateIDs .= array_pop($keys);
		}
	}
	
	/**
	 * @see wcf\form\Form::save()
	 */
	public function save() {
		parent::save();
		
		// save search
		$search = SearchEditor::create(array(
			'userID' => WCF::getUser()->userID,
			'searchData' => $this->packageUpdateServerIDs,
			'searchTime' => TIME_NOW,
			'searchType' => 'packages'
		));
		
		$this->saved();
		
		// forward
		HeaderUtil::redirect('index.php?page=PackageUpdateSearchResult&searchID='.$search->searchID.''.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * @see wcf\page\Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->updateServers = PackageUpdateServer::getActiveUpdateServers();
	}

	/**
	 * @see wcf\page\Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'updateServers' => $this->updateServers,
			'packageName' => $this->packageName,
			'searchDescription' => $this->searchDescription,
			'author' => $this->author,
			'standalone' => $this->standalone,
			'plugin' => $this->plugin,
			'other' => $this->other,
			'packageUpdateServerIDs' => $this->packageUpdateServerIDs,
			'ignoreUniques' => $this->ignoreUniques
		));
	}
	
	/**
	 * @see wcf\page\Page::assignVariables()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
