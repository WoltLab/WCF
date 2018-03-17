<?php
declare(strict_types=1);
namespace wcf\system\package\plugin;
use wcf\data\object\type\definition\ObjectTypeDefinitionEditor;
use wcf\system\devtools\pip\DevtoolsPipEntryList;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
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
	 */
	public static function getSyncDependencies() {
		return [];
	}
	
	/**
	 * @inheritDoc
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
				})),
			
			TextFormField::create('interfaceName')
				->attribute('data-tag', 'interfacename')
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
	 */
	public function getElementIdentifier(\DOMElement $element): string {
		return $element->getElementsByTagName('name')->item(0)->nodeValue;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEntryList(): IDevtoolsPipEntryList {
		$xml = $this->getProjectXml();
		$xpath = $xml->xpath();
		
		$entryList = new DevtoolsPipEntryList();
		$entryList->setKeys([
			'name' => 'wcf.acp.pip.objectTypeDefinition.definitionName',
			'interfaceName' => 'wcf.acp.pip.objectTypeDefinition.interfaceName'
		]);
		
		/** @var \DOMElement $element */
		foreach ($this->getImportElements($xpath) as $element) {
			$interfaceName = $element->getElementsByTagName('interfacename')->item(0);
			
			$entryList->addEntry($this->getElementIdentifier($element), [
				'name' => $element->getElementsByTagName('name')->item(0)->nodeValue,
				'interfaceName' => $interfaceName ? $interfaceName->nodeValue : ''
			]);
		}
		
		return $entryList;
	}
	
	/**
	 * @inheritDoc
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
