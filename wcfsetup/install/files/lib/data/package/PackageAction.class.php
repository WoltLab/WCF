<?php
namespace wcf\data\package;
use wcf\system\exception\PermissionDeniedException;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes package-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package
 * @category	Community Framework
 */
class PackageAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\package\PackageEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.system.package.canInstallPackage');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.system.package.canUninstallPackage');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.system.package.canUpdatePackage');
	
	/**
	 * package editor object
	 * @var	wcf\data\package\PackageEditor
	 */
	public $packageEditor = null;
	
	/**
	 * Validates the 'getPluginList' action.
	 */
	public function validateGetPluginList() {
		if (!isset($this->parameters['activePage']) || !intval($this->parameters['activePage'])) {
			throw new UserInputException('activePage');
		}
	}
	
	/**
	 * Returns a list of plugins.
	 * 
	 * @return	array
	 */
	public function getPluginList() {
		$pluginList = Package::getPluginList();
		$pluginList->sqlLimit = 20;
		$pluginList->sqlOffset = (($this->parameters['activePage'] - 1) * $pluginList->sqlLimit);
		$pluginList->readObjects();
		
		WCF::getTPL()->assign(array(
			'plugins' => $pluginList
		));
		return array(
			'activePage' => $this->parameters['activePage'],
			'template' => WCF::getTPL()->fetch('packageListPlugins')
		);
	}
	
	/**
	 * Validates parameters to return the confirm message for package uninstallation.
	 */
	public function validateGetConfirmMessage() {
		WCF::getSession()->checkPermissions($this->permissionsUpdate);
		
		$this->packageEditor = $this->getSingleObject();
		if (!$this->packageEditor->canUninstall()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Returns the confirm message for package uninstallation.
	 * 
	 * @return	array<string>
	 */
	public function getConfirmMessage() {
		return array(
			'confirmMessage' => WCF::getLanguage()->getDynamicVariable('wcf.acp.package.uninstallation.confirmDependentPackages', array(
				'package' => $this->packageEditor->getDecoratedObject(),
				'showDependentPackages' => true
			))
		);
	}
}
