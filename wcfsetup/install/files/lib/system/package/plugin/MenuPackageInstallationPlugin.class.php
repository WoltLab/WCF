<?php
namespace wcf\system\package\plugin;
use wcf\data\box\Box;
use wcf\data\box\BoxEditor;
use wcf\data\menu\Menu;
use wcf\data\menu\MenuEditor;
use wcf\data\menu\MenuList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Installs, updates and deletes menus.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category	Community Framework
 * @since	2.2
 */
class MenuPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * box meta data per menu
	 * @var string[]
	 */
	public $boxData = [];
	
	/**
	 * @inheritDoc
	 */
	public $className = MenuEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'menu';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM     wcf".WCF_N."_menu
			WHERE           identifier = ?
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
	 * @throws      SystemException
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$nodeValue = $element->nodeValue;
		
		if ($element->tagName === 'title') {
			if (empty($element->getAttribute('language'))) {
				throw new SystemException("Missing required attribute 'language' for menu '" . $element->parentNode->getAttribute('identifier') . "'");
			}
			
			// <title> can occur multiple times using the `language` attribute
			if (!isset($elements['title'])) $elements['title'] = [];
			
			$elements['title'][$element->getAttribute('language')] = $element->nodeValue;
		}
		else if ($element->tagName === 'box') {
			$elements['box'] = [];
			
			/** @var \DOMElement $child */
			foreach ($xpath->query('child::*', $element) as $child) {
				if ($child->tagName === 'name') {
					if (empty($child->getAttribute('language'))) {
						throw new SystemException("Missing required attribute 'language' for box name (menu '" . $element->parentNode->getAttribute('identifier') . "')");
					}
					
					// <title> can occur multiple times using the `language` attribute
					if (!isset($elements['box']['name'])) $elements['box']['name'] = [];
					
					$elements['box']['name'][$element->getAttribute('language')] = $element->nodeValue;
				}
				else {
					$elements['box'][$child->tagName] = $child->nodeValue;
				}
			}
		}
		else {
			$elements[$element->tagName] = $nodeValue;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$identifier = $data['attributes']['identifier'];
		
		if (!empty($data['elements']['box'])) {
			$position = $data['elements']['box']['position'];
			
			if ($identifier === 'com.woltlab.wcf.MainMenu') {
				$position = 'mainMenu';
			}
			else if (!in_array($position, Box::$availableMenuPositions)) {
				throw new SystemException("Unknown box position '{$position}' for menu box '{$identifier}'");
			}
			
			$this->boxData[$identifier] = [
				'identifier' => $identifier,
				'name' => $this->getI18nValues($data['elements']['title'], true),
				'boxType' => 'menu',
				'position' => $position,
				'showHeader' => (!empty($data['elements']['box']['showHeader']) ? 1 : 0),
				'visibleEverywhere' => (!empty($data['elements']['box']['visibleEverywhere']) ? 1 : 0),
				'cssClassName' => (!empty($data['elements']['box']['cssClassName'])) ? $data['elements']['box']['cssClassName'] : '',
				'originIsSystem' => 1,
				'packageID' => $this->installation->getPackageID()
			];
			
			unset($data['elements']['box']);
		}
		
		return [
			'identifier' => $identifier,
			'title' => $this->getI18nValues($data['elements']['title']),
			'originIsSystem' => 1
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_menu
			WHERE	identifier = ?
				AND packageID = ?";
		$parameters = array(
			$data['identifier'],
			$this->installation->getPackageID()
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function import(array $row, array $data) {
		// updating menus is not supported because the only modifiable data is the
		// title and overwriting it could conflict with user changes
		if (!empty($row)) {
			return new Menu(null, $row);
		}
		
		return parent::import($row, $data);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function postImport() {
		if (empty($this->boxData)) return;
		
		// all boxes belonging to the identifiers
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("identifier IN (?)", [array_keys($this->boxData)]);
		$conditions->add("packageID = ?", [$this->installation->getPackageID()]);
		
		$sql = "SELECT  *
			FROM    wcf".WCF_N."_box
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$boxes = [];
		while ($box = $statement->fetchObject(Box::class)) {
			$boxes[$box->identifier] = $box;
		}
		
		// fetch all menus relevant
		$menuList = new MenuList();
		$menuList->getConditionBuilder()->add('identifier IN (?)', [array_keys($this->boxData)]);
		$menuList->readObjects();
		
		$menus = [];
		foreach ($menuList as $menu) {
			$menus[$menu->identifier] = $menu;
		}
		
		foreach ($this->boxData as $identifier => $data) {
			// connect box with menu
			if (isset($menus[$identifier])) {
				$data['menuID'] = $menus[$identifier]->menuID;
			}
			
			if (isset($boxes[$identifier])) {
				// skip both 'identifier' and 'packageID' as these properties are immutable
				unset($data['identifier']);
				unset($data['packageID']);
				
				$boxEditor = new BoxEditor($boxes[$identifier]);
				$boxEditor->update($data);
			}
			else {
				BoxEditor::create($data);
			}
		}
	}
}
