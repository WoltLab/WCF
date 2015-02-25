<?php
namespace wcf\system\package\plugin;
use wcf\data\page\menu\item\PageMenuItemEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Installs, updates and deletes page page menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class PageMenuPackageInstallationPlugin extends AbstractMenuPackageInstallationPlugin {
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\page\menu\item\PageMenuItemEditor';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$result = parent::prepareImport($data);
		
		// position
		$result['menuPosition'] = (!empty($data['elements']['position']) && $data['elements']['position'] == 'footer') ? 'footer' : 'header';
		
		// class name
		if (!empty($data['elements']['classname'])) {
			$result['className'] = $data['elements']['classname'];
		}
		
		// validate controller and link (cannot be empty at the same time)
		if (empty($result['menuItemLink']) && empty($result['menuItemController'])) {
			throw new SystemException("Menu item '".$result['menuItem']."' neither has a link nor a controller given");
		}
		
		$result['originIsSystem'] = 1;
		
		return $result;
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::cleanup()
	 */
	protected function cleanup() {
		PageMenuItemEditor::updateLandingPage();
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::import()
	 */
	protected function import(array $row, array $data) {
		if (!empty($row)) {
			// ignore show order if null
			if ($data['showOrder'] === null) {
				unset($data['showOrder']);
			}
			else if ($data['showOrder'] != $row['showOrder']) {
				$data['showOrder'] = $this->getMenuItemPosition($data);
			}
		}
		else {
			$data['showOrder'] = $this->getMenuItemPosition($data);
		}
		
		parent::import($row, $data);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::getShowOrder()
	 */
	protected function getShowOrder($showOrder, $parentName = null, $columnName = null, $tableNameExtension = '') {
		// will be recalculated anyway
		return $showOrder;
	}
	
	/**
	 * Returns menu item position.
	 * 
	 * @param	array		$data
	 * @return	integer
	 */
	protected function getMenuItemPosition(array $data) {
		if ($data['showOrder'] === null) {
			// get greatest showOrder value
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("menuPosition = ?", array($data['menuPosition']));
			if ($data['parentMenuItem']) $conditions->add("parentMenuItem = ?", array($data['parentMenuItem']));
			
			$sql = "SELECT	MAX(showOrder) AS showOrder
				FROM	wcf".WCF_N."_".$this->tableName."
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$maxShowOrder = $statement->fetchArray();
			return (!$maxShowOrder) ? 1 : ($maxShowOrder['showOrder'] + 1);
		}
		else {
			// increase all showOrder values which are >= $showOrder
			$sql = "UPDATE	wcf".WCF_N."_".$this->tableName."
				SET	showOrder = showOrder + 1
				WHERE	showOrder >= ?
					AND menuPosition = ?
					AND parentMenuItem = ".($data['parentMenuItem'] ? "?" : "''");
			$statement = WCF::getDB()->prepareStatement($sql);
			
			$parameters = array(
				$data['showOrder'],
				$data['menuPosition']
			);
			if ($data['parentMenuItem']) $parameters[] = $data['parentMenuItem'];
			
			$statement->execute($parameters);
			
			// return the wanted showOrder level
			return $data['showOrder'];
		}
	}
}
