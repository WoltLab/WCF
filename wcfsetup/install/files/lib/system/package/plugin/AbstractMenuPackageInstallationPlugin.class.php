<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Abstract implementation of a package installation plugin for menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
abstract class AbstractMenuPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	".$this->application.WCF_N."_".$this->tableName."
			WHERE		menuItem = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute(array(
				$item['attributes']['name'],
				$this->installation->getPackageID()
			));
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		// adjust show order
		$showOrder = (isset($data['elements']['showorder'])) ? $data['elements']['showorder'] : null;
		$parent = (isset($data['elements']['parent'])) ? $data['elements']['parent'] : '';
		$showOrder = $this->getShowOrder($showOrder, $parent, 'parentMenuItem');
		
		// merge values and default values
		return array(
			'menuItem' => $data['attributes']['name'],
			'menuItemController' => isset($data['elements']['controller']) ? $data['elements']['controller'] : '',
			'menuItemLink' => (isset($data['elements']['link'])) ? $data['elements']['link'] : '',
			'options' => (isset($data['elements']['options'])) ? $data['elements']['options'] : '',
			'parentMenuItem' => (isset($data['elements']['parent'])) ? $data['elements']['parent'] : '',
			'permissions' => (isset($data['elements']['permissions'])) ? $data['elements']['permissions'] : '',
			'showOrder' => $showOrder
		);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::validateImport()
	 */
	protected function validateImport(array $data) {
		if (empty($data['parentMenuItem'])) {
			return;
		}
		
		$sql = "SELECT	COUNT(menuItemID) AS count
			FROM	".$this->application.WCF_N."_".$this->tableName."
			WHERE	menuItem = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($data['parentMenuItem']));
		$row = $statement->fetchArray();
		
		if (!$row['count']) {
			throw new SystemException("Unable to find parent 'menu item' with name '".$data['parentMenuItem']."' for 'menu item' with name '".$data['menuItem']."'.");
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	".$this->application.WCF_N."_".$this->tableName."
			WHERE	menuItem = ?
				AND packageID = ?";
		$parameters = array(
			$data['menuItem'],
			$this->installation->getPackageID()
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
}
