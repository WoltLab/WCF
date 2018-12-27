<?php
namespace wcf\system\package\plugin;
use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobEditor;
use wcf\system\cronjob\ICronjob;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\option\OptionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\CronjobUtil;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes cronjobs.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Package\Plugin
 */
class CronjobPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IGuiPackageInstallationPlugin {
	use TXmlGuiPackageInstallationPlugin;
	
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
			'canBeDisabled' => isset($data['elements']['canbedisabled']) ? intval($data['elements']['canbedisabled']) : 1,
			'canBeEdited' => isset($data['elements']['canbeedited']) ? intval($data['elements']['canbeedited']) : 1,
			'className' => isset($data['elements']['classname']) ? $data['elements']['classname'] : '',
			'cronjobName' => isset($data['attributes']['name']) ? $data['attributes']['name'] : '',
			'description' => isset($data['elements']['description']) ? $data['elements']['description'] : '',
			'isDisabled' => isset($data['elements']['isdisabled']) ? intval($data['elements']['isdisabled']) : 0,
			'options' => isset($data['elements']['options']) ? StringUtil::normalizeCsv($data['elements']['options']) : '',
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
	protected function addFormFields(IFormDocument $form) {
		/** @var IFormContainer $dataContainer */
		$dataContainer = $form->getNodeById('data');
		
		$dataContainer->appendChildren([
			TextFormField::create('cronjobName')
				->objectProperty('name')
				->label('wcf.acp.pip.cronjob.cronjobName')
				->description('wcf.acp.pip.cronjob.cronjobName.description')
				->required(),
			
			TextFormField::create('description')
				->label('wcf.global.description')
				->description('wcf.acp.pip.cronjob.description.description')
				->i18n()
				->languageItemPattern('__NONE__'),
			
			ClassNameFormField::create()
				->objectProperty('classname')
				->implementedInterface(ICronjob::class)
				->required(),
			
			OptionFormField::create()
				->description('wcf.acp.pip.cronjob.options.description'),
			
			BooleanFormField::create('isDisabled')
				->objectProperty('isdisabled')
				->label('wcf.acp.pip.cronjob.isDisabled')
				->description('wcf.acp.pip.cronjob.isDisabled.description'),
			
			BooleanFormField::create('canBeEdited')
				->objectProperty('canbeedited')
				->label('wcf.acp.pip.cronjob.canBeEdited')
				->description('wcf.acp.pip.cronjob.canBeEdited.description')
				->value(true),
			
			BooleanFormField::create('canBeDisabled')
				->objectProperty('canbedisabled')
				->label('wcf.acp.pip.cronjob.canBeDisabled')
				->description('wcf.acp.pip.cronjob.canBeDisabled.description')
				->value(true)
		]);
		
		foreach (['startMinute', 'startHour', 'startDom', 'startMonth', 'startDow'] as $timeProperty) {
			$dataContainer->insertBefore(
				TextFormField::create($timeProperty)
					->objectProperty(strtolower($timeProperty))
					->label('wcf.acp.cronjob.' . $timeProperty)
					->description("wcf.acp.cronjob.{$timeProperty}.description")
					->required()
					->addValidator(new FormFieldValidator('format', function(TextFormField $formField) use ($timeProperty) {
						try {
							CronjobUtil::validateAttribute($timeProperty, $formField->getSaveValue());
						}
						catch (SystemException $e) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'format',
									"wcf.acp.pip.cronjob.{$timeProperty}.error.format"
								)
							);
						}
					})),
				'options'
			);
		}
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function fetchElementData(\DOMElement $element, $saveData) {
		$data = [
			'className' => $element->getElementsByTagName('classname')->item(0)->nodeValue,
			'cronjobName' => $element->getAttribute('name'),
			'description' => [],
			'packageID' => $this->installation->getPackage()->packageID,
			'startDom' => $element->getElementsByTagName('startdom')->item(0)->nodeValue,
			'startDow' => $element->getElementsByTagName('startdow')->item(0)->nodeValue,
			'startHour' => $element->getElementsByTagName('starthour')->item(0)->nodeValue,
			'startMinute' => $element->getElementsByTagName('startminute')->item(0)->nodeValue,
			'startMonth' => $element->getElementsByTagName('startmonth')->item(0)->nodeValue
		];
		
		$canBeDisabled = $element->getElementsByTagName('canbedisabled')->item(0);
		if ($canBeDisabled !== null) {
			$data['canBeDisabled'] = $canBeDisabled->nodeValue;
		}
		
		$descriptionElements = $element->getElementsByTagName('description');
		$descriptions = [];
		
		/** @var \DOMElement $description */
		foreach ($descriptionElements as $description) {
			$descriptions[$description->getAttribute('language')] = $description->nodeValue;
		}
		
		foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
			if (!empty($descriptions)) {
				if (isset($descriptions[$language->languageCode])) {
					$data['description'][$language->languageID] = $descriptions[$language->languageCode];
				}
				else if (isset($descriptions[''])) {
					$data['description'][$language->languageID] = $descriptions[''];
				}
				else if (isset($descriptions['en'])) {
					$data['description'][$language->languageID] = $descriptions['en'];
				}
				else if (isset($descriptions[WCF::getLanguage()->getFixedLanguageCode()])) {
					$data['description'][$language->languageID] = $descriptions[WCF::getLanguage()->getFixedLanguageCode()];
				}
				else {
					$data['description'][$language->languageID] = reset($descriptions);
				}
			}
			else {
				$data['description'][$language->languageID] = '';
			}
		}
		
		$canBeEdited = $element->getElementsByTagName('canbeedited')->item(0);
		if ($canBeEdited !== null) {
			$data['canBeEdited'] = $canBeEdited->nodeValue;
		}
		else if ($saveData) {
			$data['canBeEdited'] = 1;
		}
		
		$canBeDisabled = $element->getElementsByTagName('canbedisabled')->item(0);
		if ($canBeDisabled !== null) {
			$data['canBeDisabled'] = $canBeDisabled->nodeValue;
		}
		else if ($saveData) {
			$data['canBeDisabled'] = 1;
		}
		
		$isDisabled = $element->getElementsByTagName('isdisabled')->item(0);
		if ($isDisabled !== null) {
			$data['isDisabled'] = $isDisabled->nodeValue;
		}
		else if ($saveData) {
			$data['isDisabled'] = 0;
		}
		
		$isDisabled = $element->getElementsByTagName('options')->item(0);
		if ($isDisabled !== null) {
			$data['options'] = $isDisabled->nodeValue;
		}
		else if ($saveData) {
			$data['options'] = '';
		}
		
		if ($saveData) {
			$descriptions = $data['description'];
			unset($data['description']);
			
			$data['description'] = [];
			foreach ($descriptions as $languageID => $description) {
				$data['description'][LanguageFactory::getInstance()->getLanguage($languageID)->languageCode] = $description;
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
			'cronjobName' => 'wcf.acp.pip.cronjob.cronjobName',
			'className' => 'wcf.form.field.className'
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form) {
		$data = $form->getData();
		$formData = $form->getData()['data'];
		
		$cronjob = $document->createElement($this->tagName);
		$cronjob->setAttribute('name', $formData['name']);
		$cronjob->appendChild($document->createElement('classname', $formData['classname']));
		
		if (isset($formData['description'])) {
			if ($formData['description'] !== '') {
				$cronjob->appendChild($document->createElement('description', $formData['description']));
			}
		}
		else if (isset($data['description_i18n'])) {
			/** @var \DOMElement $firstDescription */
			$firstDescription = null;
			foreach ($data['description_i18n'] as $languageItem => $description) {
				if ($description !== '') {
					$descriptionElement = $document->createElement('description', $description);
					$languageCode = LanguageFactory::getInstance()->getLanguage($languageItem)->languageCode;
					if ($languageCode !== 'en') {
						$descriptionElement->setAttribute('language', $languageCode);
						$cronjob->appendChild($descriptionElement);
					}
					else if ($firstDescription === null) {
						$cronjob->appendChild($descriptionElement);
					}
					else {
						// default description should be shown first
						$cronjob->insertBefore($descriptionElement, $firstDescription);
					}
					
					if ($firstDescription === null) {
						$firstDescription = $descriptionElement;
					}
				}
			}
		}
		
		$this->appendElementChildren(
			$cronjob,
			[
				'startminute',
				'starthour',
				'startdom',
				'startmonth',
				'startdow',
				'options' => '',
				'canbeedited' => 1,
				'canbedisabled' => 1,
				'isdisabled' => 0
			],
			$form
		);
		
		return $cronjob;
	}
}
