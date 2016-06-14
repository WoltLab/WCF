<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Abstract implementation of a package installation plugin for menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
abstract class AbstractMenuPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	".$this->application.WCF_N."_".$this->tableName."
			WHERE		menuItem = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['name'],
				$this->installation->getPackageID()
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		// adjust show order
		$showOrder = (isset($data['elements']['showorder'])) ? $data['elements']['showorder'] : null;
		$parent = (isset($data['elements']['parent'])) ? $data['elements']['parent'] : '';
		$showOrder = $this->getShowOrder($showOrder, $parent, 'parentMenuItem');
		
		// merge values and default values
		return [
			'menuItem' => $data['attributes']['name'],
			'menuItemController' => isset($data['elements']['controller']) ? $data['elements']['controller'] : '',
			'menuItemLink' => (isset($data['elements']['link'])) ? $data['elements']['link'] : '',
			'options' => (isset($data['elements']['options'])) ? $data['elements']['options'] : '',
			'parentMenuItem' => (isset($data['elements']['parent'])) ? $data['elements']['parent'] : '',
			'permissions' => (isset($data['elements']['permissions'])) ? $data['elements']['permissions'] : '',
			'showOrder' => $showOrder
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateImport(array $data) {
		if (empty($data['parentMenuItem'])) {
			return;
		}
		
		$sql = "SELECT	COUNT(menuItemID)
			FROM	".$this->application.WCF_N."_".$this->tableName."
			WHERE	menuItem = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$data['parentMenuItem']]);
		
		if (!$statement->fetchSingleColumn()) {
			throw new SystemException("Unable to find parent 'menu item' with name '".$data['parentMenuItem']."' for 'menu item' with name '".$data['menuItem']."'.");
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	".$this->application.WCF_N."_".$this->tableName."
			WHERE	menuItem = ?
				AND packageID = ?";
		$parameters = [
			$data['menuItem'],
			$this->installation->getPackageID()
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
}
