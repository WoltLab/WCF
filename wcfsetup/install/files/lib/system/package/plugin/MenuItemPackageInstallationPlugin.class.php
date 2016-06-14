<?php
namespace wcf\system\package\plugin;
use wcf\data\menu\item\MenuItem;
use wcf\data\menu\item\MenuItemEditor;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Installs, updates and deletes menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Package\Plugin
 * @since	3.0
 */
class MenuItemPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = MenuItemEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'item';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_menu_item
			WHERE		identifier = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['identifier'],
				$this->installation->getPackageID()
			]);
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * @inheritDoc
	 * @throws	SystemException
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$nodeValue = $element->nodeValue;
		
		if ($element->tagName === 'title') {
			if (empty($element->getAttribute('language'))) {
				throw new SystemException("Missing required attribute 'language' for menu item '" . $element->parentNode->getAttribute('identifier') . "'");
			}
			
			// <title> can occur multiple times using the `language` attribute
			if (!isset($elements['title'])) $elements['title'] = [];
			
			$elements['title'][$element->getAttribute('language')] = $element->nodeValue;
		}
		else {
			$elements[$element->tagName] = $nodeValue;
		}
	}
	
	/**
	 * @inheritDoc
	 * @throws	SystemException
	 */
	protected function prepareImport(array $data) {
		$menuID = null;
		if (!empty($data['elements']['menu'])) {
			$sql = "SELECT	menuID
				FROM	wcf".WCF_N."_menu
				WHERE	identifier = ?";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute([$data['elements']['menu']]);
			$row = $statement->fetchSingleRow();
			if ($row === false) {
				throw new SystemException("Unable to find menu '" . $data['elements']['menu'] . "' for menu item '" . $data['attributes']['identifier'] . "'");
			}
			
			$menuID = $row['menuID'];
		}
		
		$parentItemID = null;
		if (!empty($data['elements']['parent'])) {
			if ($menuID !== null) {
				throw new SystemException("The menu item '" . $data['attributes']['identifier'] . "' can either have an associated menu or a parent menu item, but not both.");
			}
			
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_menu_item
				WHERE	identifier = ?";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute([$data['elements']['parent']]);
			
			/** @var MenuItem|null $parent */
			$parent = $statement->fetchObject(MenuItem::class);
			if ($parent === null) {
				throw new SystemException("Unable to find parent menu item '" . $data['elements']['parent'] . "' for menu item '" . $data['attributes']['identifier'] . "'");
			}
			
			$parentItemID = $parent->itemID;
			$menuID = $parent->menuID;
		}
		
		if ($menuID === null && $parentItemID === null) {
			throw new SystemException("The menu item '" . $data['attributes']['identifier'] . "' must either have an associated menu or a parent menu item.");
		}
		
		$pageID = null;
		if (!empty($data['elements']['page'])) {
			$sql = "SELECT	pageID
				FROM	wcf".WCF_N."_page
				WHERE	identifier = ?";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute([$data['elements']['page']]);
			$row = $statement->fetchSingleRow();
			if ($row === false) {
				throw new SystemException("Unable to find page '" . $data['elements']['page'] . "' for menu item '" . $data['attributes']['identifier'] . "'");
			}
			
			$pageID = $row['pageID'];
		}
		
		$externalURL = (!empty($data['elements']['externalURL'])) ? $data['elements']['externalURL'] : '';
		
		if ($pageID === null && empty($externalURL)) {
			throw new SystemException("The menu item '" . $data['attributes']['identifier'] . "' must either have an associated page or an external url set.");
		}
		else if ($pageID !==  null && !empty($externalURL)) {
			throw new SystemException("The menu item '" . $data['attributes']['identifier'] . "' can either have an associated page or an external url, but not both.");
		}
		
		return [
			'externalURL' => $externalURL,
			'identifier' => $data['attributes']['identifier'],
			'menuID' => $menuID,
			'originIsSystem' => 1,
			'pageID' => $pageID,
			'parentItemID' => $parentItemID,
			'showOrder' => $this->getItemOrder($menuID, $parentItemID),
			'title' => $this->getI18nValues($data['elements']['title'])
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_menu_item
			WHERE	identifier = ?
				AND packageID = ?";
		$parameters = [
			$data['identifier'],
			$this->installation->getPackageID()
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function import(array $row, array $data) {
		// updating menu items is not supported because all fields that could be modified
		// would potentially overwrite changes made by the user
		if (!empty($row)) {
			return new MenuItem(null, $row);
		}
		
		return parent::import($row, $data);
	}
	
	/**
	 * Returns the show order for a new item that will append it to the current
	 * menu or parent item.
	 * 
	 * @param	integer		$menuID
	 * @param	integer		$parentItemID
	 * @return	integer
	 * @throws	\wcf\system\database\DatabaseException
	 */
	protected function getItemOrder($menuID, $parentItemID = null) {
		$sql = "SELECT	MAX(showOrder) AS showOrder
			FROM	wcf".WCF_N."_menu_item
			WHERE	" . ($parentItemID === null ? 'menuID' : 'parentItemID') . " = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute([
			($parentItemID === null ? $menuID : $parentItemID)
		]);
		
		$row = $statement->fetchSingleRow();
		
		return (!$row['showOrder']) ? 1 : $row['showOrder'] + 1;
	}
}
