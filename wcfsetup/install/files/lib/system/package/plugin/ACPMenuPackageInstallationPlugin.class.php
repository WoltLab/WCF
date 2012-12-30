<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\util\ClassUtil;

/**
 * Installs, updates and deletes ACP menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class ACPMenuPackageInstallationPlugin extends AbstractMenuPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\acp\menu\item\ACPMenuItemEditor';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$result = parent::prepareImport($data);
		
		// controller
		$result['menuItemController'] = (isset($data['elements']['controller'])) ? $data['elements']['controller'] : '';
		if (!empty($result['menuItemController'])) {
			if (!ClassUtil::isInstanceOf($result['menuItemController'], 'wcf\action\IAction') && !ClassUtil::isInstanceOf($result['menuItemController'], 'wcf\page\IPage')) {
				throw new SystemException("Menu item controller '".$result['menuItemController']."' is not a valid page controller");
			}
		}
		
		// validate controller and link (cannot be empty at the same time)
		if (empty($result['menuItemLink']) && empty($result['menuItemController'])) {
			throw new SystemException("Menu item '".$result['menuItem']."' neither has a link nor a controller given");
		}
		
		return $result;
	}
}
