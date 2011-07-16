<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\util\ClassUtil;

/**
 * This PIP installs, updates or deletes page page menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class PageMenuPackageInstallationPlugin extends AbstractMenuPackageInstallationPlugin {
	/**
	 * @see wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\page\menu\item\PageMenuItemEditor';
	
	/**
	 * @see wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'page_menu_item';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tagName
	 */	
	public $tagName = 'pagemenuitem';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$result = parent::prepareImport($data);
		
		// position
		$result['menuPosition'] = (!empty($data['elements']['position']) && $data['elements']['position'] == 'footer') ? 'footer' : 'header';
		// class name
		if (!empty($data['elements']['classname'])) {
			/*if (!class_exists($data['elements']['classname'])) {
				throw new SystemException("Unable to find class '".$data['elements']['classname']."'");
			}
			
			if (!ClassUtil::isInstanceOf($data['elements']['classname'], 'wcf\system\menu\page\PageMenuItemProvider')) {
				throw new SystemException($data['elements']['classname']." should implement wcf\system\menu\page\PageMenuItemProvider");
			}*/
			
			$result['className'] = $data['elements']['classname'];
		}
		
		return $result;
	}
}
