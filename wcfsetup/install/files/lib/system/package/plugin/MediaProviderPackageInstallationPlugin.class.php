<?php
namespace wcf\system\package\plugin;
use wcf\data\bbcode\media\provider\BBCodeMediaProviderEditor;
use wcf\system\cache\builder\BBCodeMediaProviderCacheBuilder;
use wcf\system\devtools\pip\IIdempotentPackageInstallationPlugin;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes media providers.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class MediaProviderPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IIdempotentPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = BBCodeMediaProviderEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'provider';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?
					AND name = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute([
				$this->installation->getPackageID(),
				$item['attributes']['name']
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		return [
			'name' => $data['attributes']['name'],
			'html' => isset($data['elements']['html']) ? $data['elements']['html'] : '',
			'className' => isset($data['elements']['className']) ? $data['elements']['className'] : '',
			'title' => $data['elements']['title'],
			'regex' => StringUtil::unifyNewlines($data['elements']['regex'])
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	packageID = ?
				AND name = ?";
		$parameters = [
			$this->installation->getPackageID(),
			$data['name']
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function cleanup() {
		// clear cache immediately
		BBCodeMediaProviderCacheBuilder::getInstance()->reset();
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getSyncDependencies() {
		return [];
	}
}
