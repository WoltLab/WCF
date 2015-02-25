<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Installs, updates and deletes object types.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category	Community Framework
 */
class ObjectTypePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\object\type\ObjectTypeEditor';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tagName
	 */
	public $tagName = 'type';
	
	/**
	 * list of names of tags which aren't considered as additional data
	 * @var	array<string>
	 */
	public static $reservedTags = array('classname', 'definitionname', 'name');
	
	/**
	 * Gets the definition id by name
	 * 
	 * @param	string		$definitionName
	 * @return	integer
	 */
	protected function getDefinitionID($definitionName) {
		// get object type id
		$sql = "SELECT	definitionID
			FROM	wcf".WCF_N."_object_type_definition
			WHERE	definitionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute(array($definitionName));
		$row = $statement->fetchArray();
		if (empty($row['definitionID'])) throw new SystemException("unknown object type definition '".$definitionName."' given");
		return $row['definitionID'];
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		objectType = ?
					AND definitionID = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute(array(
				$item['attributes']['name'],
				$this->getDefinitionID($item['elements']['definitionname']),
				$this->installation->getPackageID()
			));
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$additionalData = array();
		foreach ($data['elements'] as $tagName => $nodeValue) {
			if (!in_array($tagName, self::$reservedTags)) $additionalData[$tagName] = $nodeValue;
		}
		
		return array(
			'definitionID' => $this->getDefinitionID($data['elements']['definitionname']),
			'objectType' => $data['elements']['name'],
			'className' => (isset($data['elements']['classname']) ? $data['elements']['classname'] : ''),
			'additionalData' => serialize($additionalData)
		);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	objectType = ?
				AND definitionID = ?
				AND packageID = ?";
		$parameters = array(
			$data['objectType'],
			$data['definitionID'],
			$this->installation->getPackageID()
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
}
