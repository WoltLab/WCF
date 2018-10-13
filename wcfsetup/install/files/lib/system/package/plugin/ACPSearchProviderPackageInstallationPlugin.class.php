<?php
namespace wcf\system\package\plugin;
use wcf\data\acp\search\provider\ACPSearchProviderEditor;
use wcf\data\acp\search\provider\ACPSearchProviderList;
use wcf\system\cache\builder\ACPSearchProviderCacheBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\search\acp\IACPSearchResultProvider;
use wcf\system\WCF;

/**
 * Installs, updates and deletes ACP search providers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class ACPSearchProviderPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IGuiPackageInstallationPlugin {
	use TXmlGuiPackageInstallationPlugin;
	
	/**
	 * @inheritDoc
	 */
	public $className = ACPSearchProviderEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		providerName = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['name'],
				$this->installation->getPackageID()
			]);
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		// get show order
		$showOrder = isset($data['elements']['showorder']) ? $data['elements']['showorder'] : null;
		$showOrder = $this->getShowOrder($showOrder);
		
		return [
			'className' => $data['elements']['classname'],
			'providerName' => $data['attributes']['name'],
			'showOrder' => $showOrder
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	providerName = ?
				AND packageID = ?";
		$parameters = [
			$data['providerName'],
			$this->installation->getPackageID()
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
		ACPSearchProviderCacheBuilder::getInstance()->reset();
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()
	 * @since	3.0
	 */
	public static function getDefaultFilename() {
		return 'acpSearchProvider.xml';
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
	protected function getElementData(\DOMElement $element, $saveData = false) {
		$data = [
			'className' => $element->getElementsByTagName('classname')->item(0)->nodeValue,
			'packageID' => $this->installation->getPackage()->packageID,
			'providerName' => $element->getAttribute('name')
		];
		
		$showOrder = $element->getElementsByTagName('showorder')->item(0);
		if ($showOrder) {
			$data['showOrder'] = $showOrder->nodeValue;
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
	public function addFormFields(IFormDocument $form) {
		/** @var FormContainer $dataContainer */
		$dataContainer = $form->getNodeById('data');
		
		$dataContainer->appendChildren([
			TextFormField::create('providerName')
				->objectProperty('name')
				->label('wcf.acp.pip.acpSearchProvider.providerName')
				->description('wcf.acp.pip.acpSearchProvider.providerName.description', ['project' => $this->installation->getProject()])
				->required()
				->addValidator(ObjectTypePackageInstallationPlugin::getObjectTypeAlikeValueValidator('wcf.acp.pip.acpSearchProvider.providerName'))
				->addValidator(new FormFieldValidator('uniqueness', function(TextFormField $formField) {
					if (
						$formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE ||
						$this->editedEntry->getAttribute('name') !== $formField->getValue()
					) {
						$providerList = new ACPSearchProviderList();
						$providerList->getConditionBuilder()->add('providerName <> ?', [$formField->getValue()]);
						
						if ($providerList->countObjects() > 0) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'notUnique',
									'wcf.acp.pip.acpSearchProvider.providerName.error.notUnique'
								)
							);
						}
					}
				})),
			
			ClassNameFormField::create('className')
				->objectProperty('classname')
				->required()
				->implementedInterface(IACPSearchResultProvider::class),
			
			IntegerFormField::create('showOrder')
				->objectProperty('showorder')
				->label('wcf.acp.pip.acpSearchProvider.showOrder')
				->description('wcf.acp.pip.acpSearchProvider.showOrder.description')
				->nullable()
				->minimum(1),
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function setEntryListKeys(IDevtoolsPipEntryList $entryList) {
		$entryList->setKeys([
			'providerName' => 'wcf.acp.pip.acpSearchProvider.providerName',
			'className' => 'wcf.form.field.className'
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function sortDocument(\DOMDocument $document) {
		$this->sortImportDelete($document);
		
		$this->sortChildNodes($document->getElementsByTagName('import'), function(\DOMElement $element1, \DOMElement $element2) {
			$showOrder1 = PHP_INT_MAX;
			if ($element1->getElementsByTagName('showorder')->length === 1) {
				$showOrder1 = $element1->getElementsByTagName('showorder')->item(0)->nodeValue;
			}
			
			$showOrder2 = PHP_INT_MAX;
			if ($element2->getElementsByTagName('showorder')->length === 1) {
				$showOrder2 = $element2->getElementsByTagName('showorder')->item(0)->nodeValue;
			}
			
			if ($showOrder1 !== $showOrder2) {
				return $showOrder1 > $showOrder2;
			}
			
			return strcmp(
				$element1->getAttribute('name'),
				$element2->getAttribute('name')
			);
		});
		$this->sortChildNodes($document->getElementsByTagName('delete'), function(\DOMElement $element1, \DOMElement $element2) {
			return strcmp(
				$element1->getAttribute('name'),
				$element2->getAttribute('name')
			);
		});
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function writeEntry(\DOMDocument $document, IFormDocument $form) {
		$data = $form->getData()['data'];
		
		$acpSearchProvider = $document->createElement($this->tagName);
		$acpSearchProvider->setAttribute('name', $data['name']);
		$acpSearchProvider->appendChild($document->createElement('classname', $data['classname']));
		
		/** @var IntegerFormField $showOrder */
		if (!empty($data['showOrder'])) {
			$acpSearchProvider->appendChild($document->createElement('showorder', (string) $data['showorder']));
		}
		
		$document->getElementsByTagName('import')->item(0)->appendChild($acpSearchProvider);
		
		return $acpSearchProvider;
	}
}
