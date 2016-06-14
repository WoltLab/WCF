<?php
namespace wcf\system\package\plugin;
use wcf\system\event\EventHandler;
use wcf\system\package\PackageArchive;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;

/**
 * Abstract implementation of a package installation plugin.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
abstract class AbstractPackageInstallationPlugin implements IPackageInstallationPlugin {
	/**
	 * table application prefix
	 * @var	string
	 */
	public $application = 'wcf';
	
	/**
	 * database table name
	 * @var	string
	 */
	public $tableName = '';
	
	/**
	 * active instance of PackageInstallationDispatcher
	 * @var	\wcf\system\package\PackageInstallationDispatcher
	 */
	public $installation = null;
	
	/**
	 * install/update instructions
	 * @var	array
	 */
	public $instruction = [];
	
	/**
	 * Creates a new AbstractPackageInstallationPlugin object.
	 * 
	 * @param	\wcf\system\package\PackageInstallationDispatcher	$installation
	 * @param	array							$instruction
	 */
	public function __construct(PackageInstallationDispatcher $installation, $instruction = []) {
		$this->installation = $installation;
		$this->instruction = $instruction;
		
		// call 'construct' event
		EventHandler::getInstance()->fireAction($this, 'construct');
	}
	
	/**
	 * @inheritDoc
	 */
	public function install() {
		// call 'install' event
		EventHandler::getInstance()->fireAction($this, 'install');
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		// call 'update' event
		EventHandler::getInstance()->fireAction($this, 'update');
		
		$this->install();
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasUninstall() {
		// call 'hasUninstall' event
		EventHandler::getInstance()->fireAction($this, 'hasUninstall');
		
		$sql = "SELECT	COUNT(*)
			FROM	".$this->application.WCF_N."_".$this->tableName."
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->installation->getPackageID()]);
		
		return $statement->fetchSingleColumn() > 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function uninstall() {
		// call 'uninstall' event
		EventHandler::getInstance()->fireAction($this, 'uninstall');
		
		$sql = "DELETE FROM	".$this->application.WCF_N."_".$this->tableName."
			WHERE		packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->installation->getPackageID()]);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()
	 * @since	3.0
	 */
	public static function getDefaultFilename() {
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function isValid(PackageArchive $archive, $instruction) {
		return true;
	}
}
