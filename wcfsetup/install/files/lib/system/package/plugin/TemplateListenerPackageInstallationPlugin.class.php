<?php
declare(strict_types=1);
namespace wcf\system\package\plugin;
use wcf\data\acp\template\ACPTemplate;
use wcf\data\acp\template\ACPTemplateList;
use wcf\data\template\listener\TemplateListenerEditor;
use wcf\data\template\listener\TemplateListenerList;
use wcf\data\template\Template;
use wcf\data\template\TemplateList;
use wcf\system\cache\builder\TemplateListenerCodeCacheBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes template listeners.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class TemplateListenerPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IGuiPackageInstallationPlugin {
	use TXmlGuiPackageInstallationPlugin;
	
	/**
	 * @inheritDoc
	 */
	public $className = TemplateListenerEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?
					AND environment = ?
					AND eventName = ?
					AND name = ?
					AND templateName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute([
				$this->installation->getPackageID(),
				$item['elements']['environment'],
				$item['elements']['eventname'],
				$item['attributes']['name'],
				$item['elements']['templatename']
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$niceValue = isset($data['elements']['nice']) ? intval($data['elements']['nice']) : 0;
		if ($niceValue < -128) {
			$niceValue = -128;
		}
		else if ($niceValue > 127) {
			$niceValue = 127;
		}
		
		return [
			'environment' => $data['elements']['environment'],
			'eventName' => $data['elements']['eventname'],
			'niceValue' => $niceValue,
			'name' => $data['attributes']['name'],
			'options' => isset($data['elements']['options']) ? StringUtil::normalizeCsv($data['elements']['options']) : '',
			'permissions' => isset($data['elements']['permissions']) ? StringUtil::normalizeCsv($data['elements']['permissions']) : '',
			'templateCode' => $data['elements']['templatecode'],
			'templateName' => $data['elements']['templatename']
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	packageID = ?
				AND name = ?
				AND templateName = ?
				AND eventName = ?
				AND environment = ?";
		$parameters = [
			$this->installation->getPackageID(),
			$data['name'],
			$data['templateName'],
			$data['eventName'],
			$data['environment']
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
		TemplateListenerCodeCacheBuilder::getInstance()->reset();
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
		$ldq = preg_quote(WCF::getTPL()->getCompiler()->getLeftDelimiter(), '~');
		$rdq = preg_quote(WCF::getTPL()->getCompiler()->getRightDelimiter(), '~');
		
		$getEvents = function($templateList) use ($ldq, $rdq) {
			$templateEvents = [];
			/** @var ACPTemplate|Template $template */
			foreach ($templateList as $template) {
				if (preg_match_all("~{$ldq}event\ name\=\'(?<event>[\w]+)\'{$rdq}~", $template->getSource(), $matches)) {
					$templates[$template->templateName] = $template->templateName;
					
					foreach ($matches['event'] as $event) {
						if (!isset($templateEvents[$template->templateName])) {
							$templateEvents[$template->templateName] = [];
						}
						
						$templateEvents[$template->templateName][] = $event;
					}
				}
			}
			
			foreach ($templateEvents as &$events) {
				sort($events);
			}
			unset($events);
			
			return $templateEvents;
		};
		
		$templateList = new TemplateList();
		$templateList->getConditionBuilder()->add(
			'template.packageID IN (?)',
			[array_keys($this->installation->getPackage()->getAllRequiredPackages())]
		);
		$templateList->getConditionBuilder()->add('template.templateGroupID IS NULL');
		$templateList->sqlOrderBy = 'template.templateName ASC';
		$templateList->readObjects();
		
		$templateEvents = $getEvents($templateList);
		
		$acpTemplateList = new ACPTemplateList();
		$acpTemplateList->getConditionBuilder()->add(
			'acp_template.packageID IN (?)',
			[array_keys($this->installation->getPackage()->getAllRequiredPackages())]
		);
		$acpTemplateList->sqlOrderBy = 'acp_template.templateName ASC';
		$acpTemplateList->readObjects();
		
		$acpTemplateEvents = $getEvents($acpTemplateList);
		
		$form->getNodeById('data')->appendChildren([
			TextFormField::create('name')
				->label('wcf.acp.pip.templateListener.name')
				->description('wcf.acp.pip.templateListener.name.description')
				->required()
				->addValidator(new FormFieldValidator('format', function(TextFormField $formField) {
					if (!preg_match('~^[a-z][A-z]+$~', $formField->getValue())) {
						$formField->addValidationError(
							new FormFieldValidationError(
								'format',
								'wcf.acp.pip.templateListener.name.error.format'
							)
						);
					}
				})),
			
			SingleSelectionFormField::create('templateName')
				->objectProperty('templatename')
				->label('wcf.acp.pip.templateListener.templateName')
				->description('wcf.acp.pip.templateListener.templateName.description')
				->required()
				->options(array_combine(array_keys($templateEvents), array_keys($templateEvents)))
				->filterable(),
			
			SingleSelectionFormField::create('acpTemplateName')
				->objectProperty('templatename')
				->label('wcf.acp.pip.templateListener.templateName')
				->description('wcf.acp.pip.templateListener.templateName.description')
				->required()
				->options(array_combine(array_keys($acpTemplateEvents), array_keys($acpTemplateEvents)))
				->filterable()
		]);
		
		foreach ($templateEvents as $templateName => $events) {
			$form->getNodeById('data')->appendChild(
				SingleSelectionFormField::create($templateName . '_eventName')
					->objectProperty('eventname')
					->label('wcf.acp.pip.templateListener.eventName')
					->description('wcf.acp.pip.templateListener.eventName.description')
					->required()
					->options(array_combine($events, $events))
					->addDependency(
						ValueFormFieldDependency::create('templateName')
							->field($form->getNodeById('templateName'))
							->values([$templateName])
					)
			);
		}
		
		foreach ($acpTemplateEvents as $templateName => $events) {
			$form->getNodeById('data')->appendChild(
				SingleSelectionFormField::create('acp_' . $templateName . '_eventName')
					->objectProperty('eventname')
					->label('wcf.acp.pip.templateListener.eventName')
					->description('wcf.acp.pip.templateListener.eventName.description')
					->required()
					->options(array_combine($events, $events))
					->addDependency(
						ValueFormFieldDependency::create('acpTemplateName')
							->field($form->getNodeById('acpTemplateName'))
							->values([$templateName])
					)
			);
		}
		
		$form->getNodeById('data')->appendChildren([
			SingleSelectionFormField::create('environment')
				->label('wcf.acp.pip.templateListener.environment')
				->description('wcf.acp.pip.templateListener.environment.description')
				->required()
				->options([
					'admin' => 'admin',
					'user' => 'user'
				])
				->value('user')
				->addValidator(new FormFieldValidator('uniqueness', function(SingleSelectionFormField $formField) {
					$listenerList = new TemplateListenerList();
					$listenerList->getConditionBuilder()->add(
						'name = ?',
						[$formField->getDocument()->getNodeById('name')->getSaveValue()]
					);
					
					if ($formField->getSaveValue() === 'admin') {
						$templateName = $formField->getDocument()->getNodeById('acpTemplateName')->getSaveValue();
						$eventName = $formField->getDocument()->getNodeById('acp_' . $templateName . '_eventName')->getSaveValue();
					}
					else {
						$templateName = $formField->getDocument()->getNodeById('templateName')->getSaveValue();
						$eventName = $formField->getDocument()->getNodeById($templateName . '_eventName')->getSaveValue();
					}
					
					$listenerList->getConditionBuilder()->add('templateName = ?', [$templateName]);
					
					$listenerList->getConditionBuilder()->add('eventName = ?', [$eventName]);
					$listenerList->getConditionBuilder()->add('environment = ?', [$formField->getSaveValue()]);
					
					if ($listenerList->countObjects() > 0) {
						$formField->getDocument()->getNodeById('name')->addValidationError(
							new FormFieldValidationError(
								'notUnique',
								'wcf.acp.pip.templateListener.name.error.notUnique'
							)
						);
					}
				})),
			
			// TODO: use field with code support
			MultilineTextFormField::create('templateCode')
				->objectProperty('templatecode')
				->label('wcf.acp.pip.templateListener.templateCode')
				->description('wcf.acp.pip.templateListener.templateCode.description')
				->required()
		]);
		
		$form->getNodeById('templateName')->addDependency(
			ValueFormFieldDependency::create('environment')
				->field($form->getNodeById('environment'))
				->values(['user'])
		);
		$form->getNodeById('acpTemplateName')->addDependency(
			ValueFormFieldDependency::create('environment')
				->field($form->getNodeById('environment'))
				->values(['admin'])
		);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function getElementData(\DOMElement $element, bool $saveData = false): array {
		return [
			'environment' => $element->getElementsByTagName('environment')->item(0)->nodeValue,
			'eventName' => $element->getElementsByTagName('eventname')->item(0)->nodeValue,
			'name' => $element->getAttribute('name'),
			'packageID' => $this->installation->getPackage()->packageID,
			'templateCode' => $element->getElementsByTagName('templatecode')->item(0)->nodeValue,
			'templateName' => $element->getElementsByTagName('templatename')->item(0)->nodeValue
		];
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getElementIdentifier(\DOMElement $element): string {
		return sha1(
			$element->getElementsByTagName('templatename')->item(0)->nodeValue . '/' .
			$element->getElementsByTagName('eventname')->item(0)->nodeValue . '/' .
			$element->getElementsByTagName('environment')->item(0)->nodeValue . '/' .
			$element->getAttribute('name')
		);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function setEntryListKeys(IDevtoolsPipEntryList $entryList) {
		$entryList->setKeys([
			'name' => 'wcf.acp.pip.templateListener.name',
			'templateName' => 'wcf.acp.pip.templateListener.templateName',
			'eventName' => 'wcf.acp.pip.templateListener.eventName',
			'environment' => 'wcf.acp.pip.templateListener.environment'
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function sortDocument(\DOMDocument $document) {
		$this->sortImportDelete($document);
		
		$compareFunction = function(\DOMElement $element1, \DOMElement $element2) {
			$templateName1 = $element1->getElementsByTagName('templatename')->item(0)->nodeValue;
			$templateName2 = $element2->getElementsByTagName('templatename')->item(0)->nodeValue;
			
			if ($templateName1 !== $templateName2) {
				return strcmp($templateName1, $templateName2);
			}
			
			$eventName1 = $element1->getElementsByTagName('eventname')->item(0)->nodeValue;
			$eventName2 = $element2->getElementsByTagName('eventname')->item(0)->nodeValue;
			
			if ($eventName1 !== $eventName2) {
				return strcmp($eventName1, $eventName2);
			}
			
			return strcmp(
				$element1->getElementsByTagName('environment')->item(0)->nodeValue,
				$element2->getElementsByTagName('environment')->item(0)->nodeValue
			);
		};
		
		$this->sortChildNodes($document->getElementsByTagName('import'), $compareFunction);
		$this->sortChildNodes($document->getElementsByTagName('delete'), $compareFunction);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function writeEntry(\DOMDocument $document, IFormDocument $form): \DOMElement {
		$listener = $document->createElement($this->tagName);
		$listener->setAttribute('name', $form->getNodeById('name')->getSaveValue());
		
		$environment = $form->getNodeById('environment')->getSaveValue();
		if ($environment === 'user') {
			$templateName = $form->getNodeById('templateName')->getSaveValue();
			
			$listener->appendChild($document->createElement('templatename', $templateName));
			$listener->appendChild($document->createElement('eventname', $form->getNodeById($templateName . '_eventName')->getSaveValue()));
		}
		else {
			$templateName = $form->getNodeById('acpTemplateName')->getSaveValue();
			
			$listener->appendChild($document->createElement('templatename', $templateName));
			$listener->appendChild($document->createElement('eventname', $form->getNodeById('acp_' . $templateName . '_eventName')->getSaveValue()));
		}
		$listener->appendChild($document->createElement('templatecode', '<![CDATA[' . StringUtil::unifyNewlines(StringUtil::escapeCDATA($form->getNodeById('templateCode')->getSaveValue())) . ']]>'));
		$listener->appendChild($document->createElement('environment', $environment));
		
		$document->getElementsByTagName('import')->item(0)->appendChild($listener);
		
		return $listener;
	}
}
