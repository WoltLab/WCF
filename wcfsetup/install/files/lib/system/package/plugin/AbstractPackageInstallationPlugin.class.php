<?php
namespace wcf\system\package\plugin;
use wcf\system\event\EventHandler;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;

/**
 * Default implementation of some PackageInstallationPlugin functions.
 * 
 * @author	Benjamin Kunz
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
abstract class AbstractPackageInstallationPlugin implements PackageInstallationPlugin {
	/**
	 * database table name
	 * @var string
	 */
	public $tableName = '';
	
	/**
	 * active instance of PackageInstallationQueue
	 * @var PackageInstallationQueue
	 */
	public $installation = null;
	
	/**
	 * install/update instructions
	 * @var array
	 */
	public $instruction = array();
	
	/**
	 * Creates a new AbstractPackageInstallationPlugin object.
	 * 
	 * @param 	PackageInstallationDispatcher	$installation
	 * @param	array				$instruction
	 */
	public function __construct(PackageInstallationDispatcher $installation, $instruction = array()) {
		$this->installation = $installation;
		$this->instruction = $instruction;
		
		// call construct event
		EventHandler::getInstance()->fireAction($this, 'construct');
	}
	
	/**
	 * @see	 PackageInstallationPlugin::install()
	 */
	public function install() {
		// call install event
		EventHandler::getInstance()->fireAction($this, 'install');
	}
	
	/**
	 * @see	 PackageInstallationPlugin::update()
	 */
	public function update() {
       		// call update event
		EventHandler::getInstance()->fireAction($this, 'update');
				
		return $this->install();
	}
	
	/**
	 * @see	 PackageInstallationPlugin::hasUninstall()
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
	 * @see	 PackageInstallationPlugin::uninstall()
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
