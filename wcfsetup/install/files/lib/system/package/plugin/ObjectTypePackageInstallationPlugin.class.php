<?php
namespace wcf\system\package\plugin;
use wcf\data\object\type\ObjectTypeEditor;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Installs, updates and deletes object types.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Package\Plugin
 */
class ObjectTypePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = ObjectTypeEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'type';
	
	/**
	 * list of names of tags which aren't considered as additional data
	 * @var	string[]
	 */
	public static $reservedTags = ['classname', 'definitionname', 'name'];
	
	/**
	 * Gets the definition id by name
	 * 
	 * @param	string		$definitionName
	 * @return	integer
	 * @throws	SystemException
	 */
	protected function getDefinitionID($definitionName) {
		// get object type id
		$sql = "SELECT	definitionID
			FROM	wcf".WCF_N."_object_type_definition
			WHERE	definitionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute([$definitionName]);
		$row = $statement->fetchArray();
		if (empty($row['definitionID'])) throw new SystemException("unknown object type definition '".$definitionName."' given");
		return $row['definitionID'];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		objectType = ?
					AND definitionID = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['name'],
				$this->getDefinitionID($item['elements']['definitionname']),
				$this->installation->getPackageID()
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$additionalData = [];
		foreach ($data['elements'] as $tagName => $nodeValue) {
			if (!in_array($tagName, self::$reservedTags)) $additionalData[$tagName] = $nodeValue;
		}
		
		return [
			'definitionID' => $this->getDefinitionID($data['elements']['definitionname']),
			'objectType' => $data['elements']['name'],
			'className' => (isset($data['elements']['classname']) ? $data['elements']['classname'] : ''),
			'additionalData' => serialize($additionalData)
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	objectType = ?
				AND definitionID = ?
				AND packageID = ?";
		$parameters = [
			$data['objectType'],
			$data['definitionID'],
			$this->installation->getPackageID()
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
}
