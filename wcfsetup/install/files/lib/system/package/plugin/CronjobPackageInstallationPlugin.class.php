<?php
namespace wcf\system\package\plugin;
use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobEditor;
use wcf\system\WCF;
use wcf\util\CronjobUtil;

/**
 * Installs, updates and deletes cronjobs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Package\Plugin
 */
class CronjobPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = CronjobEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		if ($element->tagName == 'description') {
			if (!isset($elements['description'])) {
				$elements['description'] = [];
			}
			
			$elements['description'][$element->getAttribute('language')] = $element->nodeValue;
		}
		else {
			parent::getElement($xpath, $elements, $element);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		className = ?
					AND packageID = ?";
		$legacyStatement = WCF::getDB()->prepareStatement($sql);
		
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		cronjobName = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		foreach ($items as $item) {
			if (!isset($item['attributes']['name'])) {
				$legacyStatement->execute([
					$item['elements']['classname'],
					$this->installation->getPackageID()
				]);
			}
			else {
				$statement->execute([
					$item['attributes']['name'],
					$this->installation->getPackageID()
				]);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		return [
			'canBeDisabled' => (isset($data['elements']['canbedisabled'])) ? intval($data['elements']['canbedisabled']) : 1,
			'canBeEdited' => (isset($data['elements']['canbeedited'])) ? intval($data['elements']['canbeedited']) : 1,
			'className' => (isset($data['elements']['classname'])) ? $data['elements']['classname'] : '',
			'cronjobName' => (isset($data['attributes']['name']) ? $data['attributes']['name'] : ''),
			'description' => (isset($data['elements']['description'])) ? $data['elements']['description'] : '',
			'isDisabled' => (isset($data['elements']['isdisabled'])) ? intval($data['elements']['isdisabled']) : 0,
			'options' => (isset($data['elements']['options'])) ? $data['elements']['options'] : '',
			'startDom' => $data['elements']['startdom'],
			'startDow' => $data['elements']['startdow'],
			'startHour' => $data['elements']['starthour'],
			'startMinute' => $data['elements']['startminute'],
			'startMonth' => $data['elements']['startmonth']
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateImport(array $data) {
		CronjobUtil::validate($data['startMinute'], $data['startHour'], $data['startDom'], $data['startMonth'], $data['startDow']);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function import(array $row, array $data) {
		// if a cronjob is updated without a name given, keep the old automatically
		// assigned name
		if (!empty($row) && !$data['cronjobName']) {
			unset($data['cronjobName']);
		}
		
		/** @var Cronjob $cronjob */
		$cronjob = parent::import($row, $data);
		
		// update cronjob name
		if (!$cronjob->cronjobName) {
			$cronjobEditor = new CronjobEditor($cronjob);
			$cronjobEditor->update([
				'cronjobName' => Cronjob::AUTOMATIC_NAME_PREFIX.$cronjob->cronjobID
			]);
			
			$cronjob = new Cronjob($cronjob->cronjobID);
		}
		
		return $cronjob;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		if (!$data['cronjobName']) return null;
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	packageID = ?
				AND cronjobName = ?";
		$parameters = [
			$this->installation->getPackageID(),
			$data['cronjobName']
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareCreate(array &$data) {
		parent::prepareCreate($data);
		
		$data['nextExec'] = TIME_NOW;
	}
}
