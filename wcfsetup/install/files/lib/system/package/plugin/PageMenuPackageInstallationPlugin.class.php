<?php
namespace wcf\system\package\plugin;

/**
 * This PIP installs, updates or deletes page page menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class PageMenuPackageInstallationPlugin extends AbstractMenuPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\page\menu\item\PageMenuItemEditor';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$result = parent::prepareImport($data);
		
		// position
		$result['menuPosition'] = (!empty($data['elements']['position']) && $data['elements']['position'] == 'footer') ? 'footer' : 'header';
		// class name
		if (!empty($data['elements']['classname'])) {
			$result['className'] = $data['elements']['classname'];
		}
		
		return $result;
	}
}
