<?php
namespace wcf\system\package\plugin;

/**
 * Installs, updates and deletes ACP menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class ACPMenuPackageInstallationPlugin extends AbstractMenuPackageInstallationPlugin {
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\acp\menu\item\ACPMenuItemEditor';
	
	/**
	 * @see \wcf\system\package\plugin\AbstractMenuPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$returnValue = parent::prepareImport($data);
		
		$returnValue['icon'] = (isset($data['elements']['icon'])) ? $data['elements']['icon'] : '';
		
		return $returnValue;
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()
	 */
	public static function getDefaultFilename() {
		return 'acpMenu.xml';
	}
}
