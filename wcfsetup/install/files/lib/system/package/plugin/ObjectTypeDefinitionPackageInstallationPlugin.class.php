<?php
declare(strict_types=1);
namespace wcf\system\package\plugin;
use wcf\data\object\type\definition\ObjectTypeDefinitionEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;

/**
 * Installs, updates and deletes object type definitions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Package\Plugin
 */
class ObjectTypeDefinitionPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IGuiPackageInstallationPlugin {
	use TXmlGuiPackageInstallationPlugin;
	
	/**
	 * @inheritDoc
	 */
	public $className = ObjectTypeDefinitionEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'definition';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		definitionName = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['name'],
				$this->installation->getPackageID()
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		return [
			'interfaceName' => isset($data['elements']['interfacename']) ? $data['elements']['interfacename'] : '',
			'definitionName' => $data['elements']['name'],
			'categoryName' => isset($data['elements']['categoryname']) ? $data['elements']['categoryname'] : ''
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	definitionName = ?";
		$parameters = [$data['definitionName']];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
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
		$form->getNodeById('data')->appendChildren([
			TextFormField::create('name')
				->label('wcf.acp.pip.objectTypeDefinition.definitionName')
				->description('wcf.acp.pip.objectTypeDefinition.definitionName.description', ['project' => $this->installation->getProject()])
				->required()
				->addValidator(new FormFieldValidator('format', function(TextFormField $formField) {
					if ($formField->getValue()) {
						$segments = explode('.', $formField->getValue());
						if (count($segments) < 4) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'tooFewSegments',
									'wcf.acp.pip.objectTypeDefinition.definitionName.error.tooFewSegments',
									['segmentCount' => count($segments)]
								)
							);
						}
						else {
							$invalidSegments = [];
							foreach ($segments as $key => $segment) {
								if (!preg_match('~^[A-z0-9\-\_]+$~', $segment)) {
									$invalidSegments[$key] = $segment;
								}
							}
							
							if (!empty($invalidSegments)) {
								$formField->addValidationError(
									new FormFieldValidationError(
										'invalidSegments',
										'wcf.acp.pip.objectTypeDefinition.definitionName.error.invalidSegments',
										['invalidSegments' => $invalidSegments]
									)
								);
							}
						}
					}
				}))
				->addValidator(new FormFieldValidator('uniqueness', function(TextFormField $formField) {
					if ($formField->getValue()) {
						$objectTypeDefinition = ObjectTypeCache::getInstance()->getDefinitionByName($formField->getValue());
						
						// the definition name is not unique if such an object type definition
						// already exists and (a) a new definition is added or (b) an existing
						// definition is edited but the new definition name is not the old definition
						// name so that the existing definition is not the definition currently edited
						if ($objectTypeDefinition !== null && (
							$formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE ||
							$this->editedEntry->getElementsByTagName('name')->item(0)->nodeValue !== $formField->getValue()
						)) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'notUnique',
									'wcf.acp.pip.objectTypeDefinition.definitionName.error.notUnique'
								)
							);
						}
					}
				})),
			
			TextFormField::create('interfaceName')
				->objectProperty('interfacename')
				->label('wcf.acp.pip.objectTypeDefinition.interfaceName')
				->description('wcf.acp.pip.objectTypeDefinition.interfaceName.description')
				->addValidator(new FormFieldValidator('interfaceExists', function(TextFormField $formField) {
					if ($formField->getValue() && !interface_exists($formField->getValue())) {
						$formField->addValidationError(
							new FormFieldValidationError(
								'nonExistent',
								'wcf.acp.pip.objectTypeDefinition.interfaceName.error.nonExistent'
							)
						);
					}
				}))
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function getElementData(\DOMElement $element, bool $saveData = false): array {
		$data = [
			'definitionName' => $element->getElementsByTagName('name')->item(0)->nodeValue,
			'packageID' => $this->installation->getPackage()->packageID
		];
		
		$interfaceName = $element->getElementsByTagName('interfacename')->item(0);
		if ($interfaceName) {
			$data['interfaceName'] = $interfaceName->nodeValue;
		}
		
		return $data;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getElementIdentifier(\DOMElement $element): string {
		return $element->getElementsByTagName('name')->item(0)->nodeValue;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function setEntryListKeys(IDevtoolsPipEntryList $entryList) {
		$entryList->setKeys([
			'definitionName' => 'wcf.acp.pip.objectTypeDefinition.definitionName',
			'interfaceName' => 'wcf.acp.pip.objectTypeDefinition.interfaceName'
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function sortDocument(\DOMDocument $document) {
		$this->sortImportDelete($document);
		
		$this->sortChildNodes($document->getElementsByTagName('import'), function(\DOMElement $element1, \DOMElement $element2) {
			return strcmp(
				$element1->getElementsByTagName('name')->item(0)->nodeValue,
				$element2->getElementsByTagName('name')->item(0)->nodeValue
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
	protected function writeEntry(\DOMDocument $document, IFormDocument $form): \DOMElement {
		$definition = $document->createElement('definition');
		$definition->appendChild($document->createElement('name', $form->getNodeById('name')->getSaveValue()));
		
		/** @var TextFormField $interfaceName */
		$interfaceName = $form->getNodeById('interfaceName');
		if ($interfaceName->getSaveValue()) {
			$definition->appendChild($document->createElement('interfacename', $interfaceName->getSaveValue()));
		}
		
		$import = $document->getElementsByTagName('import')->item(0);
		$import->appendChild($definition);
		
		return $definition;
	}
}
