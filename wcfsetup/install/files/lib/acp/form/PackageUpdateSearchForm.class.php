<?php
namespace wcf\acp\form;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\search\SearchEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\ArrayUtil;
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
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canInstallPackage');
	
	/**
	 * @see wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.database';
	
	/**
	 * list of package update server ids which are searched
	 * @var	array<integer>
	 */
	public $packageUpdateServerIDs = array();
	
	/**
	 * searched package name
	 * @var	string
	 */
	public $packageName = '';
	
	/**
	 * searched package author
	 * @var	string
	 */
	public $author = '';
	
	/**
	 * indicates if package description is searched
	 * @var	integer
	 */
	public $searchDescription = 0;
	
	/**
	 * indicates if plugins for already installed packages are searched
	 * @var	integer
	 */
	public $plugin = 1;
	
	/**
	 * indicates if applications are searched
	 * @var	integer
	 */
	public $isApplication = 1;
	
	/**
	 * indicates if packages that aren't plugins or applications are searched
	 * @var	integer
	 */
	public $other = 0;
	
	/**
	 * indicates if unique packages are ignored that are already installed
	 * @var	integer
	 */
	public $ignoreUniques = 1;
	
	/**
	 * list of available update servers
	 * @var	array<wcf\data\package\update\server\PackageUpdateServer>
	 */
	public $updateServers = array();
	
	/**
	 * ids of package updates
	 * @var	string
	 */
	public $packageUpdateIDs = '';
	
	/**
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->ignoreUniques = $this->plugin = $this->isApplication = 0;
		if (isset($_POST['packageUpdateServerIDs']) && is_array($_POST['packageUpdateServerIDs'])) $this->packageUpdateServerIDs = ArrayUtil::toIntegerArray($_POST['packageUpdateServerIDs']);
		if (isset($_POST['packageName'])) $this->packageName = StringUtil::trim($_POST['packageName']);
		if (isset($_POST['author'])) $this->author = StringUtil::trim($_POST['author']);
		if (isset($_POST['searchDescription'])) $this->searchDescription = intval($_POST['searchDescription']);
		if (isset($_POST['plugin'])) $this->plugin = intval($_POST['plugin']);
		if (isset($_POST['isApplication'])) $this->isApplication = intval($_POST['isApplication']);
		if (isset($_POST['other'])) $this->other = intval($_POST['other']);
		if (isset($_POST['ignoreUniques'])) $this->ignoreUniques = intval($_POST['ignoreUniques']);
	}
	
	/**
	 * @see wcf\form\IForm::validate()
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
		if (($this->plugin == 0 || $this->isApplication == 0 || $this->other == 0) && ($this->plugin == 1 || $this->isApplication == 1 || $this->other == 1)) {
			if ($this->isApplication == 1) {
				$condition = 'isApplication = 1';
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
					$condition .= " OR isApplication = 0";
				}
				
				$conditions->add('('.$condition.')');
			}
			else if ($this->other) {
				$conditions->add("(isApplication = 0 AND plugin = '')");
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
	 * @see wcf\form\IForm::save()
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
		$url = LinkHandler::getInstance()->getLink('PackageUpdateSearchResult', array('id' => $search->searchID));
		HeaderUtil::redirect($url);
		exit;
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->updateServers = PackageUpdateServer::getActiveUpdateServers();
	}

	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'updateServers' => $this->updateServers,
			'packageName' => $this->packageName,
			'searchDescription' => $this->searchDescription,
			'author' => $this->author,
			'isApplication' => $this->isApplication,
			'plugin' => $this->plugin,
			'other' => $this->other,
			'packageUpdateServerIDs' => $this->packageUpdateServerIDs,
			'ignoreUniques' => $this->ignoreUniques
		));
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
