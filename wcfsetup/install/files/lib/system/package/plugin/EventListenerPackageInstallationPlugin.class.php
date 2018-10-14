<?php
namespace wcf\system\package\plugin;
use wcf\data\event\listener\EventListener;
use wcf\data\event\listener\EventListenerEditor;
use wcf\data\event\listener\EventListenerList;
use wcf\system\cache\builder\EventListenerCacheBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\OptionFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\UserGroupOptionFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes event listeners.
 * 
 * @author	Matthias Schmidt, Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class EventListenerPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IGuiPackageInstallationPlugin {
	use TXmlGuiPackageInstallationPlugin;
	
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
					isset($item['elements']['environment']) ? $item['elements']['environment'] : 'user',
					$item['elements']['eventclassname'],
					$item['elements']['eventname'],
					isset($item['elements']['inherit']) ? $item['elements']['inherit'] : 0,
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
		$nice = isset($data['elements']['nice']) ? intval($data['elements']['nice']) : 0;
		if ($nice < -128) $nice = -128;
		else if ($nice > 127) $nice = 127;
		
		return [
			'environment' => isset($data['elements']['environment']) ? $data['elements']['environment'] : 'user',
			'eventClassName' => $data['elements']['eventclassname'],
			'eventName' => StringUtil::normalizeCsv($data['elements']['eventname']),
			'inherit' => isset($data['elements']['inherit']) ? intval($data['elements']['inherit']) : 0,
			'listenerClassName' => $data['elements']['listenerclassname'],
			'listenerName' => isset($data['attributes']['name']) ? $data['attributes']['name'] : '',
			'niceValue' => $nice,
			'options' => isset($data['elements']['options']) ? StringUtil::normalizeCsv($data['elements']['options']) : '',
			'permissions' => isset($data['elements']['permissions']) ? StringUtil::normalizeCsv($data['elements']['permissions']) : ''
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
	
	/**
	 * @inheritDoc
	 * @since	3.1
	 */
	public static function getSyncDependencies() {
		return [];
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function addFormFields(IFormDocument $form) {
		/** @var FormContainer $dataContainer */
		$dataContainer = $form->getNodeById('data');
		
		$dataContainer->appendChildren([
			TextFormField::create('listenerName')
				->label('wcf.acp.pip.eventListener.listenerName')
				->description('wcf.acp.pip.eventListener.listenerName.description')
				->required()
				->addValidator(new FormFieldValidator('format', function(TextFormField $formField) {
					if (preg_match('~^[a-z][A-z0-9]*$~', $formField->getSaveValue()) !== 1) {
						$formField->addValidationError(
							new FormFieldValidationError(
								'format',
								'wcf.acp.pip.eventListener.listenerName.error.format'
							)
						);
					}
				}))
				->addValidator(new FormFieldValidator('uniqueness', function(TextFormField $formField) {
					if (
						$formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE ||
						$this->editedEntry->getAttribute('name') !== $formField->getValue()
					) {
						$eventListenerList = new EventListenerList();
						$eventListenerList->getConditionBuilder()->add('listenerName = ?', [$formField->getValue()]);
						
						if ($eventListenerList->countObjects() > 0) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'notUnique',
									'wcf.acp.pip.eventListener.listenerName.error.notUnique'
								)
							);
						}
					}
				})),
			
			ClassNameFormField::create('eventClassName')
				->objectProperty('eventclassname')
				->label('wcf.acp.pip.eventListener.eventClassName')
				->description('wcf.acp.pip.eventListener.eventClassName.description')
				->required()
				->instantiable(false),
			
			TextFormField::create('eventName')
				->objectProperty('eventname')
				->label('wcf.acp.pip.eventListener.eventName')
				->description('wcf.acp.pip.eventListener.eventName.description')
				->required(),
			
			ClassNameFormField::create('listenerClassName')
				->objectProperty('listenerclassname')
				->label('wcf.acp.pip.eventListener.listenerClassName')
				->required()
				->implementedInterface(IParameterizedEventListener::class),
			
			SingleSelectionFormField::create('environment')
				->label('wcf.acp.pip.eventListener.environment')
				->description('wcf.acp.pip.eventListener.environment.description')
				->options([
					'admin' => 'admin',
					'user' => 'user'
				])
				->value('user'),
			
			BooleanFormField::create('inherit')
				->label('wcf.acp.pip.eventListener.inherit')
				->description('wcf.acp.pip.eventListener.inherit.description'),
			
			IntegerFormField::create('niceValue')
				->objectProperty('nice')
				->label('wcf.acp.pip.eventListener.niceValue')
				->description('wcf.acp.pip.eventListener.niceValue.description')
				->nullable()
				->minimum(-128)
				->maximum(127),
			
			OptionFormField::create()
				->description('wcf.acp.pip.eventListener.options.description')
				->packageIDs(array_merge(
					[$this->installation->getPackage()->packageID],
					array_keys($this->installation->getPackage()->getAllRequiredPackages())
				)),
			
			UserGroupOptionFormField::create()
				->description('wcf.acp.pip.eventListener.permissions.description')
				->packageIDs(array_merge(
					[$this->installation->getPackage()->packageID],
					array_keys($this->installation->getPackage()->getAllRequiredPackages())
				))
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function getElementData(\DOMElement $element, $saveData = false) {
		$data = [
			'eventClassName' => $element->getElementsByTagName('eventclassname')->item(0)->nodeValue,
			'eventName' => StringUtil::normalizeCsv($element->getElementsByTagName('eventname')->item(0)->nodeValue),
			'listenerClassName' => $element->getElementsByTagName('listenerclassname')->item(0)->nodeValue,
			'listenerName' => $element->getAttribute('name'),
			'packageID' => $this->installation->getPackage()->packageID
		];
		
		foreach (['environment', 'inherit', 'nice', 'options', 'permissions'] as $optionalElementProperty) {
			$optionalElement = $element->getElementsByTagName($optionalElementProperty)->item(0);
			if ($optionalElement !== null) {
				$data[$optionalElementProperty] = $optionalElement->nodeValue;
			}
		}
		
		return $data;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getElementIdentifier(\DOMElement $element) {
		return $element->getAttribute('name');
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function setEntryListKeys(IDevtoolsPipEntryList $entryList) {
		$entryList->setKeys([
			'listenerName' => 'wcf.acp.pip.eventListener.listenerName',
			'eventClassName' => 'wcf.acp.pip.eventListener.eventClassName',
			'eventName' => 'wcf.acp.pip.eventListener.eventName'
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function createXmlElement(\DOMDocument $document, IFormDocument $form) {
		$data = $form->getData()['data'];
		
		$eventListener = $document->createElement($this->tagName);
		$eventListener->setAttribute('name', $data['listenerName']);
		
		foreach (['eventclassname', 'eventname', 'listenerclassname'] as $property) {
			$eventListener->appendChild($document->createElement($property, $data[$property]));
		}
		
		foreach (['environment', 'inherit', 'nice', 'options', 'permissions'] as $optionalProperty) {
			if (!empty($data[$optionalProperty])) {
				$eventListener->appendChild($document->createElement($optionalProperty, (string)$data[$optionalProperty]));
			}
		}
		
		return $eventListener;
	}
}
