<?php
namespace wcf\system\package\plugin;
use wcf\system\WCF;

/**
 * Installs, updates and deletes CMS pages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category	Community Framework
 */
class PagePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\clipboard\action\ClipboardActionEditor';
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::$tagName
	 */
	public $tagName = 'page';
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		actionName = ?
					AND actionClassName = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute(array(
				$item['attributes']['name'],
				$item['elements']['actionclassname'],
				$this->installation->getPackageID()
			));
		}
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::getElement()
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$nodeValue = $element->nodeValue;
		
		// read pages
		if ($element->tagName == 'pages') {
			$nodeValue = array();
			
			$pages = $xpath->query('child::ns:page', $element);
			foreach ($pages as $page) {
				$nodeValue[] = $page->nodeValue;
			}
		}
		
		$elements[$element->tagName] = $nodeValue;
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$isStatic = (!empty($data['content']));
		
		return [
			'controller' => ($isStatic) ? '' : $data['elements']['controller'],
			'controllerCustomURL' => ($isStatic) ? '' : $data['elements']['customurl'],
			'displayName' => $data['elements']['displayname'],
			'name' => $data['attributes']['name']
		];
		
		$showOrder = (isset($data['elements']['showorder'])) ? intval($data['elements']['showorder']) : null;
		$showOrder = $this->getShowOrder($showOrder, $data['elements']['actionclassname'], 'actionClassName');
		
		return array(
			'actionClassName' => $data['elements']['actionclassname'],
			'actionName' => $data['attributes']['name'],
			'pages' => $data['elements']['pages'],
			'showOrder' => $showOrder
		);
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	actionName = ?
				AND actionClassName = ?
				AND packageID = ?";
		$parameters = array(
			$data['actionName'],
			$data['actionClassName'],
			$this->installation->getPackageID()
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::import()
	 */
	protected function import(array $row, array $data) {
		// extract pages
		$pages = $data['pages'];
		unset($data['pages']);
		
		// import or update action
		$object = parent::import($row, $data);
		
		// store pages for later import
		$this->pages[$object->actionID] = $pages;
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::postImport()
	 */
	protected function postImport() {
		// clear pages
		$sql = "DELETE FROM	wcf".WCF_N."_clipboard_page
			WHERE		packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		
		if (!empty($this->pages)) {
			// insert pages
			$sql = "INSERT INTO	wcf".WCF_N."_clipboard_page
						(pageClassName, packageID, actionID)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($this->pages as $actionID => $pages) {
				foreach ($pages as $pageClassName) {
					$statement->execute(array(
						$pageClassName,
						$this->installation->getPackageID(),
						$actionID
					));
				}
			}
		}
	}
}
