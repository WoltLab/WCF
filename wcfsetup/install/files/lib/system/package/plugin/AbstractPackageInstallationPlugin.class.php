<?php
namespace wcf\system\package\plugin;
use wcf\system\event\EventHandler;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;

/**
 * Default implementation of some PackageInstallationPlugin functions.
 * 
 * @author	Benjamin Kunz
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
abstract class AbstractPackageInstallationPlugin implements IPackageInstallationPlugin {
	/**
	 * database table name
	 * @var	string
	 */
	public $tableName = '';
	
	/**
	 * active instance of PackageInstallationDispatcher
	 * @var	wcf\system\package\PackageInstallationDispatcher
	 */
	public $installation = null;
	
	/**
	 * install/update instructions
	 * @var	array
	 */
	public $instruction = array();
	
	/**
	 * Creates a new AbstractPackageInstallationPlugin object.
	 * 
	 * @param	wcf\system\package\PackageInstallationDispatcher	$installation
	 * @param	array		$instruction
	 */
	public function __construct(PackageInstallationDispatcher $installation, $instruction = array()) {
		$this->installation = $installation;
		$this->instruction = $instruction;
		
		// call construct event
		EventHandler::getInstance()->fireAction($this, 'construct');
	}
	
	/**
	 * @see	wcf\system\package\plugin\IPackageInstallationPlugin::install()
	 */
	public function install() {
		// call install event
		EventHandler::getInstance()->fireAction($this, 'install');
	}
	
	/**
	 * @see	wcf\system\package\plugin\IPackageInstallationPlugin::update()
	 */
	public function update() {
		// call update event
		EventHandler::getInstance()->fireAction($this, 'update');
				
		return $this->install();
	}
	
	/**
	 * @see	wcf\system\package\plugin\IPackageInstallationPlugin::hasUninstall()
	 */
	public function hasUninstall() {
		// call hasUninstall event
		EventHandler::getInstance()->fireAction($this, 'hasUninstall');
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		$installationCount = $statement->fetchArray();
		return $installationCount['count'];
	}
	
	/**
	 * @see	wcf\system\package\plugin\IPackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// call uninstall event
		EventHandler::getInstance()->fireAction($this, 'uninstall');
		
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
	}
}
