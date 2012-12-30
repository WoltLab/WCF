<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\util\ClassUtil;

/**
 * Installs, updates and deletes page page menu items.
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
		
		// controller
		$result['menuItemController'] = (isset($data['elements']['controller'])) ? $data['elements']['controller'] : '';
		if (!empty($result['menuItemController'])) {
			if (!ClassUtil::isInstanceOf($result['menuItemController'], 'wcf\action\IAction') && !ClassUtil::isInstanceOf($result['menuItemController'], 'wcf\page\IPage')) {
				throw new SystemException("Menu item controller '".$result['menuItemController']."' is not a valid page controller");
			}
		}
		
		// class name
		if (!empty($data['elements']['classname'])) {
			$result['className'] = $data['elements']['classname'];
		}
		
		// validate controller and link (cannot be empty at the same time)
		if (empty($result['menuItemLink']) && empty($result['menuItemController'])) {
			throw new SystemException("Menu item '".$result['menuItem']."' neither has a link nor a controller given");
		}
		
		return $result;
	}
}
