<?php
namespace wcf\system\package\plugin;
use wcf\data\route\component\RouteComponentEditor;
use wcf\system\WCF;

/**
 * This PIP installs, updates or deletes routes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class RoutePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\route\RouteEditor';
	
	/**
	 * list of components of each route
	 * @var	array<array>
	 */
	protected $components = array();

	/**
	 * @see	wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'route';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tagName
	 */	
	public $tagName = 'route';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		routeName = ?
					AND isACPRoute = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute(array(
				$item['attributes']['name'],
				$item['elements']['isacproute'],
				$this->installation->getPackageID()
			));
		}
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::getElement()
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$nodeValue = $element->nodeValue;
		
		// read components
		if ($element->tagName == 'components') {
			$nodeValue = array();
			
			$components = $xpath->query('child::*', $element);
			foreach ($components as $component) {
				$name = $component->getAttribute('name');
				$nodeValue[$name] = array();
				
				$componentData = $xpath->query('child::*', $component);
				foreach ($componentData as $data) {
					$nodeValue[$name][$data->tagName] = $data->nodeValue; 
				}
			}
		}
		
		$elements[$element->tagName] = $nodeValue;
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		// check if components and schema match!
		
		return array(
			'routeSchema' => $data['elements']['schema'],
			'routeName' => $data['attributes']['name'],
			'components' => $data['elements']['components'],
			'controller' => isset($data['elements']['controller']) ? $data['elements']['controller'] : null,
			'isACPRoute' => isset($data['elements']['isacproute']) ? $data['elements']['isacproute'] : 0,
			'partsPattern' => isset($data['elements']['partspattern']) ? $data['elements']['partspattern'] : null
		);
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	routeName = ?
				AND isACPRoute = ?
				AND packageID = ?";
		$parameters = array(
			$data['routeName'],
			$data['isACPRoute'],
			$this->installation->getPackageID()
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::import()
	 */
	protected function import(array $row, array $data) {
		// extract components
		$components = $data['components'];
		unset($data['components']);
		
		$route = parent::import($row, $data);
		
		// store components for later import
		$this->components[$route->routeID] = $components;
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::postImport()
	 */
	protected function postImport() {
		// clear components
		$sql = "DELETE FROM	wcf".WCF_N."_route_component
			WHERE		routeID IN (
						SELECT 	routeID
						FROM	wcf".WCF_N."_".$this->tableName."
						WHERE	packageID = ?
					)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		
		if (count($this->components)) {
			foreach ($this->components as $routeID => $components) {
				foreach ($components as $name => $data) {
					RouteComponentEditor::create(array(
						'routeID' => $routeID,
						'componentName' => $name,
						'defaultValue' => (isset($data['defaultvalue']) ? $data['defaultvalue'] : null),
						'pattern' => (isset($data['pattern']) ? $data['pattern'] : null),
						'isOptional' => (isset($data['optional']) ? $data['optional'] : 0)
					));
				}
			}
		}
	}
}
