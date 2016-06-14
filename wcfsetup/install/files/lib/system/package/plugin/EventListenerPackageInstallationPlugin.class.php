<?php
namespace wcf\system\package\plugin;
use wcf\data\event\listener\EventListener;
use wcf\data\event\listener\EventListenerEditor;
use wcf\system\cache\builder\EventListenerCacheBuilder;
use wcf\system\WCF;

/**
 * Installs, updates and deletes event listeners.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class EventListenerPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = EventListenerEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'eventlistener';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?
					AND environment = ?
					AND eventClassName = ?
					AND eventName = ?
					AND inherit = ?
					AND listenerClassName = ?";
		$legacyStatement = WCF::getDB()->prepareStatement($sql);
		
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?
					AND listenerName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		foreach ($items as $item) {
			if (!isset($item['attributes']['name'])) {
				$legacyStatement->execute([
					$this->installation->getPackageID(),
					(isset($item['elements']['environment']) ? $item['elements']['environment'] : 'user'),
					$item['elements']['eventclassname'],
					$item['elements']['eventname'],
					(isset($item['elements']['inherit'])) ? $item['elements']['inherit'] : 0,
					$item['elements']['listenerclassname']
				]);
			}
			else {
				$statement->execute([
					$this->installation->getPackageID(),
					$item['attributes']['name']
				]);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$nice = (isset($data['elements']['nice'])) ? intval($data['elements']['nice']) : 0;
		if ($nice < -128) $nice = -128;
		else if ($nice > 127) $nice = 127;
		
		return [
			'environment' => (isset($data['elements']['environment']) ? $data['elements']['environment'] : 'user'),
			'eventClassName' => $data['elements']['eventclassname'],
			'eventName' => $data['elements']['eventname'],
			'inherit' => (isset($data['elements']['inherit'])) ? intval($data['elements']['inherit']) : 0,
			'listenerClassName' => $data['elements']['listenerclassname'],
			'listenerName' => (isset($data['attributes']['name']) ? $data['attributes']['name'] : ''),
			'niceValue' => $nice,
			'options' => (isset($data['elements']['options']) ? $data['elements']['options'] : ''),
			'permissions' => (isset($data['elements']['permissions']) ? $data['elements']['permissions'] : '')
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function import(array $row, array $data) {
		// if an event listener is updated without a name given, keep the
		// old automatically assigned name
		if (!empty($row) && !$data['listenerName']) {
			unset($data['listenerName']);
		}
		
		/** @var EventListener $eventListener */
		$eventListener = parent::import($row, $data);
		
		// update event listener name
		if (!$eventListener->listenerName) {
			$eventListenerEditor = new EventListenerEditor($eventListener);
			$eventListenerEditor->update([
				'listenerName' => EventListener::AUTOMATIC_NAME_PREFIX.$eventListener->listenerID
			]);
			
			$eventListener = new EventListener($eventListener->listenerID);
		}
		
		return $eventListener;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		if (!$data['listenerName']) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_".$this->tableName."
				WHERE	packageID = ?
					AND environment = ?
					AND eventClassName = ?
					AND eventName = ?
					AND listenerClassName = ?";
			$parameters = [
				$this->installation->getPackageID(),
				$data['environment'],
				$data['eventClassName'],
				$data['eventName'],
				$data['listenerClassName']
			];
		}
		else {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_".$this->tableName."
				WHERE	packageID = ?
					AND listenerName = ?";
			$parameters = [
				$this->installation->getPackageID(),
				$data['listenerName']
			];
		}
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function uninstall() {
		parent::uninstall();
		
		// clear cache immediately
		EventListenerCacheBuilder::getInstance()->reset();
	}
}
