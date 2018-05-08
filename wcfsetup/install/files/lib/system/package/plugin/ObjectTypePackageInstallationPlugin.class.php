<?php
declare(strict_types=1);
namespace wcf\system\package\plugin;
use wcf\data\object\type\definition\ObjectTypeDefinitionList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\object\type\ObjectTypeEditor;
use wcf\data\DatabaseObjectList;
use wcf\system\application\ApplicationHandler;
use wcf\system\condition\AbstractIntegerCondition;
use wcf\system\condition\UserGroupCondition;
use wcf\system\condition\UserIntegerPropertyCondition;
use wcf\system\condition\UserTimestampPropertyCondition;
use wcf\system\devtools\pip\DevtoolsPipEntryList;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\data\GuiPackageInstallationPluginFormFieldDataProcessor;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\OptionFormField;
use wcf\system\form\builder\field\UserGroupOptionFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\FloatFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\ItemListFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;

/**
 * Installs, updates and deletes object types.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Package\Plugin
 */
class ObjectTypePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IGuiPackageInstallationPlugin {
	use TXmlGuiPackageInstallationPlugin;
	
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
	 * @var	string[]
	 */
	public $definitionNames = [];
	
	/**
	 * @var	string[]
	 */
	public $definitionNamesWithInterface = [];
	
	/**
	 * Returns the id of the object type definition with the given name.
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
			'className' => isset($data['elements']['classname']) ? $data['elements']['classname'] : '',
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
	
	/**
	 * @inheritDoc
	 */
	public static function getSyncDependencies() {
		return ['objectTypeDefinition'];
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getAdditionalTemplateCode(): string {
		return WCF::getTPL()->fetch('__objectTypePipGui', 'wcf', [
			'definitionNames' => $this->definitionNames,
			'definitionNamesWithInterface' => $this->definitionNamesWithInterface
		], true);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function getElementData(\DOMElement $element): array {
		$data = [
			'definitionID' => $this->getDefinitionID($element->getElementsByTagName('definitionname')->item(0)->nodeValue),
			'objectType' => $element->getElementsByTagName('name')->item(0)->nodeValue,
			'packageID' => $this->installation->getPackage()->packageID
		];
		
		$className = $element->getElementsByTagName('classname')->item(0);
		if ($className) {
			$data['classname'] = $className->nodeValue;
		}
		
		$additionalData = [];
		
		/** @var \DOMElement $child */
		foreach ($element->childNodes as $child) {
			if (!in_array($child->nodeName, self::$reservedTags)) {
				$additionalData[$child->nodeName] = $child->nodeValue;
			}
		}
		
		$data['additionalData'] = serialize($additionalData);
		
		return $data;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function addFormFields(IFormDocument $form) {
		// add custom data processor
		$form->getDataHandler()->add(new GuiPackageInstallationPluginFormFieldDataProcessor());
		
		// read available object type definitions
		$list = new ObjectTypeDefinitionList();
		$list->sqlOrderBy = 'definitionName';
		$list->readObjects();
		
		foreach ($list as $definition) {
			$this->definitionNames[$definition->definitionName] = $definition->definitionName;
			
			if ($definition->interfaceName) {
				$this->definitionNamesWithInterface[$definition->definitionName] = $definition->interfaceName;
			}
		}
		
		// add default form fields
		$form->getNodeById('data')->appendChildren([
			SingleSelectionFormField::create('definitionName')
				->attribute('data-tag', 'definitionname')
				->label('wcf.acp.pip.objectType.definitionName')
				->description('<!-- will be replaced by JavaScript -->')
				->options($this->definitionNames)
				->required(),
			
			TextFormField::create('objectType')
				->attribute('data-tag', 'name')
				->label('wcf.acp.pip.objectType.objectType')
				->description('wcf.acp.pip.objectType.objectType.description')
				->required()
				->addValidator($this->getObjectTypeAlikeValueValidator('objectType'))
				->addValidator(new FormFieldValidator('uniqueness', function(TextFormField $formField) {
					$definitionName = $formField->getDocument()->getNodeById('definitionName')->getValue();
					if ($definitionName) {
						$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
							ObjectTypeCache::getInstance()->getDefinitionByName($definitionName)->definitionName,
							$formField->getValue()
						);
						
						// the object type name is not unique if such an object type already exists
						// and (a) a new object type is added or (b) the existing object type is
						// different from the edited object type
						if ($objectType !== null && (
								$formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE ||
								$this->editedEntry->getElementsByTagName('name')->item(0)->nodeValue !== $formField->getValue() ||
								$this->editedEntry->getElementsByTagName('definitionname')->item(0)->nodeValue !== $definitionName
							)) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'notUnique',
									'wcf.acp.pip.objectType.objectType.error.notUnique'
								)
							);
						}
					}
				})),
			
			ClassNameFormField::create('className')
				->attribute('data-tag', 'classname')
				->label('wcf.acp.pip.objectType.className')
				->description('<!-- will be replaced by JavaScript -->')
				->required()
				->addValidator(new FormFieldValidator('implementsInterface', function(TextFormField $formField) {
					$definitionName = $formField->getDocument()->getNodeById('definitionName')->getValue();
					if ($definitionName) {
						$definition = ObjectTypeCache::getInstance()->getDefinitionByName($definitionName);
						
						if (!is_subclass_of($formField->getValue(), $definition->interfaceName)) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'interface',
									'wcf.form.field.className.error.interface',
									['interface' => $definition->interfaceName]
								)
							);
						}
					}
				})),
		]);
		
		/** @var SingleSelectionFormField $definitionName */
		$definitionName = $form->getNodeById('definitionName');
		
		// add general field dependencies
		$form->getNodeById('className')->addDependency(
			ValueFormFieldDependency::create('definitionName')
				->field($definitionName)
				->values(array_keys($this->definitionNamesWithInterface))
		);
		
		// add object type-specific fields
		
		// com.woltlab.wcf.adLocation
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.adLocation')
			->appendChildren([
				TextFormField::create('adLocationCategoryName')
					->attribute('data-tag', 'categoryname')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.adLocation.categoryName')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.adLocation.categoryName.description')
					->addValidator($this->getObjectTypeAlikeValueValidator('com.woltlab.wcf.adLocation.categoryName')),
				ItemListFormField::create('adLocationCssClassName')
					->attribute('data-tag', 'cssclassname')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.adLocation.cssClassName')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.adLocation.cssClassName.description')
					->saveValueType(ItemListFormField::SAVE_VALUE_TYPE_SSV)
					->addValidator(new FormFieldValidator('format', function(ItemListFormField $formField) {
						if (!empty($formField->getValue())) {
							$invalidClasses = [];
							foreach ($formField->getValue() as $class) {
								if (preg_match('~^-?[_A-z][_A-z0-9-]*$~', $class) !== 1) {
									$invalidClasses[] = $class;
								}
							}
							
							if (!empty($invalidClasses)) {
								$formField->addValidationError(
									new FormFieldValidationError(
										'invalid',
										'wcf.acp.pip.objectType.com.woltlab.wcf.adLocation.cssClassName.error.invalid',
										['invalidClasses' => $invalidClasses]
									)
								);
							}
						}
					}))
			]);
		
		// com.woltlab.wcf.attachment.objectType
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.attachment.objectType')
			->appendChild(
				BooleanFormField::create('attachmentPrivate')
					->attribute('data-tag', 'private')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.attachment.objectType.private')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.attachment.objectType.private.description')
			);
		
		// com.woltlab.wcf.bulkProcessing.user.action
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.bulkProcessing.user.action')
			->appendChildren([
				TextFormField::create('bulkProcessingUserAction')
					->attribute('data-tag', 'action')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.bulkProcessing.user.action.action')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.bulkProcessing.user.action.action.description')
					->addValidator(new FormFieldValidator('format', function(TextFormField $formField) {
						if (!preg_match('~^[a-z][A-z]+$~', $formField->getValue())) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'format',
									'wcf.acp.pip.objectType.com.woltlab.wcf.bulkProcessing.user.action.action.error.format'
								)
							);
						}
					})),
				
				OptionFormField::create('bulkProcessingUserOptions')
					->attribute('data-tag', 'options')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.bulkProcessing.user.action.options.description')
					->packageIDs(array_merge(
						[$this->installation->getPackage()->packageID],
						array_keys($this->installation->getPackage()->getAllRequiredPackages())
					)),
				
				UserGroupOptionFormField::create('bulkProcessingUserPermissions')
					->attribute('data-tag', 'permissions')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.bulkProcessing.user.action.permissions.description')
					->packageIDs(array_merge(
						[$this->installation->getPackage()->packageID],
						array_keys($this->installation->getPackage()->getAllRequiredPackages())
					))
			]);
		
		// com.woltlab.wcf.bulkProcessing.user.condition
		$bulkProcessingUserConditionContainer = $this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.bulkProcessing.user.condition');
		$this->addConditionFields($bulkProcessingUserConditionContainer, true, true);
		
		// com.woltlab.wcf.category
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.category')
			->appendChild(
				BooleanFormField::create('categoryDefaultPermission')
					->attribute('data-tag', 'defaultpermission')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.category.defaultPermission')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.category.defaultPermission.description')
			);
		
		// com.woltlab.wcf.clipboardItem
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.clipboardItem')
			->appendChild(
				ClassNameFormField::create('clipboardItemListClassName')
					->attribute('data-tag', 'listclassname')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.clipboardItem.listClassName')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.clipboardItem.listClassName.description')
					->required()
					->parentClass(DatabaseObjectList::class)
			);
		
		// com.woltlab.wcf.condition.ad
		$conditionAdContainer = $this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.condition.ad');
		$this->addConditionFields($conditionAdContainer, true, false);
		
		// com.woltlab.wcf.condition.notice
		$conditionAdContainer = $this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.condition.notice');
		$this->addConditionFields($conditionAdContainer);
		
		// com.woltlab.wcf.condition.trophy
		$conditionAdContainer = $this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.condition.trophy');
		$this->addConditionFields($conditionAdContainer, false, true);
		
		// com.woltlab.wcf.condition.userGroupAssignment
		$conditionAdContainer = $this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.condition.userGroupAssignment');
		$this->addConditionFields($conditionAdContainer, false, true);
		
		// com.woltlab.wcf.condition.userSearch
		$conditionAdContainer = $this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.condition.userSearch');
		$this->addConditionFields($conditionAdContainer, false, true);
		
		// com.woltlab.wcf.notification.objectType
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.notification.objectType')
			->appendChild(
				TextFormField::create('notificationObjectTypeCategory')
					->attribute('data-tag', 'category')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.notification.objectType.category')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.notification.objectType.category.description')
					// TODO: validator
			);
		
		// com.woltlab.wcf.rebuildData
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.rebuildData')
			->appendChild(
				IntegerFormField::create('rebuildDataNiceValue')
					->attribute('data-tag', 'nicevalue')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.rebuildData.niceValue')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.rebuildData.niceValue.description')
					->nullable()
			);
		
		// com.woltlab.wcf.searchableObjectType
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.searchableObjectType')
			->appendChild(
				TextFormField::create('searchableObjectTypeSearchIndex')
					->attribute('data-tag', 'searchindex')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.searchableObjectType.searchIndex')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.searchableObjectType.searchIndex.description')
					->required()
					->addValidator(new FormFieldValidator('tableName', function(TextFormField $formField) {
						if ($formField->getValue()) {
							if (preg_match('~^(?P<app>[A-z]+)1_[A-z_]+$~', $formField->getValue(), $match)) {
								if (!ApplicationHandler::getInstance()->getApplication($match['app'])) {
									$formField->addValidationError(
										new FormFieldValidationError(
											'unknownApp',
											'wcf.acp.pip.objectType.com.woltlab.wcf.searchableObjectType.searchIndex.error.unknownApp',
											['app' => $match['app']]
										)
									);
								}
							}
							else {
								$formField->addValidationError(
									new FormFieldValidationError(
										'invalid',
										'wcf.acp.pip.objectType.com.woltlab.wcf.searchableObjectType.searchIndex.error.invalid'
									)
								);
							}
						}
					}))
			);
		
		// com.woltlab.wcf.sitemap.object
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.sitemap.object')
			->appendChildren([
				FloatFormField::create('sitemapObjectPriority')
					->attribute('data-tag', 'priority')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.sitemap.object.priority')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.sitemap.object.priority.description')
					->required()
					->minimum(0.0)
					->maximum(1.0)
					->step(0.1)
					->value(0.5),
				
				SingleSelectionFormField::create('sitemapObjectchangeFreq')
					->attribute('data-tag', 'changeFreq')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.sitemap.object.changeFreq')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.sitemap.object.changeFreq.description')
					->options([
						'always',
						'hourly',
						'daily',
						'weekly',
						'monthly',
						'yearly',
						'never'
					])
					->required(),
				
				IntegerFormField::create('sitemapObjectRebuildTime')
					->attribute('data-tag', 'rebuildTime')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.sitemap.object.rebuildTime')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.sitemap.object.rebuildTime.description')
					->suffix('wcf.acp.option.suffix.seconds')
					->required()
					->minimum(0)
			]);
		
		// com.woltlab.wcf.statDailyHandler
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.statDailyHandler')
			->appendChildren([
				TextFormField::create('statDailyHandlerCategoryName')
					->attribute('data-tag', 'categoryname')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.statDailyHandler.categoryName')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.statDailyHandler.categoryName.description')
					->addValidator($this->getObjectTypeAlikeValueValidator('com.woltlab.wcf.statDailyHandler.categoryName')),
				
				BooleanFormField::create('statDailyHandlerIsDefault')
					->attribute('data-tag', 'default')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.statDailyHandler.isDefault')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.statDailyHandler.isDefault.description')
			]);
		
		// com.woltlab.wcf.tagging.taggableObject
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.tagging.taggableObject')
			->appendChildren([
				OptionFormField::create('taggingTaggableObjectOptions')
					->attribute('data-tag', 'options')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.tagging.taggableObject.options.description')
					->packageIDs(array_merge(
						[$this->installation->getPackage()->packageID],
						array_keys($this->installation->getPackage()->getAllRequiredPackages())
					)),
				
				UserGroupOptionFormField::create('taggingTaggableObjectPermissions')
					->attribute('data-tag', 'permissions')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.tagging.taggableObject.permissions.description')
					->packageIDs(array_merge(
						[$this->installation->getPackage()->packageID],
						array_keys($this->installation->getPackage()->getAllRequiredPackages())
					))
			]);
		
		// com.woltlab.wcf.user.activityPointEvent
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.user.activityPointEvent')
			->appendChild(
				IntegerFormField::create('userActivityPointEventPoints')
					->attribute('data-tag', 'points')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.user.activityPointEvent.points')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.user.activityPointEvent.points.description')
					->minimum(0)
					->required()
			);
		
		// com.woltlab.wcf.versionTracker.objectType
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.versionTracker.objectType')
			->appendChildren([
				TextFormField::create('versionTrackerObjectTypeTableName')
					->attribute('data-tag', 'tableName')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tableName')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tableName.description')
					->required()
					->addValidator(new FormFieldValidator('tableExists', function(TextFormField $formField) {
						if ($formField->getValue()) {
							$value = ApplicationHandler::insertRealDatabaseTableNames($formField->getValue());
							
							if (!in_array($value, WCF::getDB()->getEditor()->getTableNames())) {
								$formField->addValidationError(new FormFieldValidationError(
									'nonExistent',
									'wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tableName.error.nonExistent',
									['tableName' => $value]
								));
							}
						}
					})),
				
				TextFormField::create('versionTrackerObjectTypeTablePrimaryKey')
					->attribute('data-tag', 'tablePrimaryKey')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tablePrimaryKey')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tablePrimaryKey.description')
					->required()
					->addValidator(new FormFieldValidator('columnExists', function(TextFormField $formField) {
						if ($formField->getValue()) {
							/** @var TextFormField $tableName */
							$tableName = $formField->getDocument()->getNodeById('versionTrackerObjectTypeTableName');
							
							if (empty($tableName->getValidationErrors())) {
								// table name has already been validated and table exists
								$columns = WCF::getDB()->getEditor()->getColumns($tableName->getValue());
								
								foreach ($columns as $column) {
									if ($column['name'] === $formField->getValue()) {
										if ($column['data']['key'] !== 'PRIMARY') {
											$formField->addValidationError(new FormFieldValidationError(
												'noPrimaryColumn',
												'wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tablePrimaryKey.error.noPrimaryColumn'
											));
										}
										
										return;
									}
								}
								
								$formField->addValidationError(new FormFieldValidationError(
									'nonExistent',
									'wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tablePrimaryKey.error.nonExistent'
								));
							}
						}
					})),
			]);
	}
	
	/**
	 * Returns a form field validator to validate a string value that has a
	 * object type-alike structure.
	 * 
	 * @param	string		$languageItemSegment	used for error language items: `wcf.acp.pip.objectType.{$languageItemSegment}.error.{errorType}`
	 * @return	FormFieldValidator
	 */
	protected function getObjectTypeAlikeValueValidator($languageItemSegment): FormFieldValidator {
		return new FormFieldValidator('format', function(TextFormField $formField) use ($languageItemSegment) {
			if ($formField->getValue()) {
				$segments = explode('.', $formField->getValue());
				if (count($segments) < 4) {
					$formField->addValidationError(
						new FormFieldValidationError(
							'tooFewSegments',
							'wcf.acp.pip.objectType.' . $languageItemSegment . '.error.tooFewSegments',
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
								'wcf.acp.pip.objectType.' . $languageItemSegment . '.error.invalidSegments',
								['invalidSegments' => $invalidSegments]
							)
						);
					}
				}
			}
		});
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getElementIdentifier(\DOMElement $element): string {
		return sha1(
			$element->getElementsByTagName('name')->item(0)->nodeValue . '/' .
			$element->getElementsByTagName('definitionname')->item(0)->nodeValue
		);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function getEmptyXml(): string {
		return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data xmlns="http://www.woltlab.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.woltlab.com http://www.woltlab.com/XSD/vortex/objectType.xsd">
	<import></import>
</data>
XML;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getEntryList(): IDevtoolsPipEntryList {
		$xml = $this->getProjectXml();
		$xpath = $xml->xpath();
		
		$entryList = new DevtoolsPipEntryList();
		$entryList->setKeys([
			'name' => 'wcf.acp.pip.objectType.objectType',
			'definitionName' => 'wcf.acp.pip.objectType.definitionName'
		]);
		
		/** @var \DOMElement $element */
		foreach ($this->getImportElements($xpath) as $element) {
			$entryList->addEntry($this->getElementIdentifier($element), [
				'name' => $element->getElementsByTagName('name')->item(0)->nodeValue,
				'definitionName' => $element->getElementsByTagName('definitionname')->item(0)->nodeValue
			]);
		}
		
		return $entryList;
	}
	
	/**
	 * Returns a form container for the object type definition-specific fields
	 * of the the object type definition with the given name.
	 * 
	 * The returned form container is already appended to the given form and
	 * has a dependency on the `definitionName` field so that the form container
	 * is only shown for the relevant object type definition.
	 * 
	 * @param	IFormDocument	$form
	 * @param	string		$definitionName
	 * @return	FormContainer
	 * @since	3.2
	 */
	protected function getObjectTypeDefinitionDataContainer(IFormDocument $form, string $definitionName): FormContainer {
		/** @var SingleSelectionFormField $definitionNameField */
		$definitionNameField = $form->getNodeById('definitionName');
		
		$definitionPieces = explode('.', $definitionName);
		
		$formContainer = FormContainer::create(lcfirst(implode('', array_map('ucfirst', $definitionPieces))) . 'Fields')
			->label('wcf.acp.pip.objectType.' . $definitionName . '.data.title')
			->addDependency(
				ValueFormFieldDependency::create('definitionName')
					->field($definitionNameField)
					->values([$definitionName])
			);
		
		$form->appendChild($formContainer);
		
		return $formContainer;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function sortDocument(\DOMDocument $document) {
		$this->sortImportDelete($document);
		
		$this->sortChildNodes($document->getElementsByTagName('import'), function(\DOMElement $element1, \DOMElement $element2) {
			return strcmp(
				$element1->getElementsByTagName('definitionname')->item(0)->nodeValue,
				$element2->getElementsByTagName('definitionname')->item(0)->nodeValue
			) ?: strcmp(
				$element1->getElementsByTagName('name')->item(0)->nodeValue,
				$element2->getElementsByTagName('name')->item(0)->nodeValue
			);
		});
		
		$this->sortChildNodes($document->getElementsByTagName('import')->item(0)->childNodes, function(\DOMElement $element1, \DOMElement $element2) {
			// force `definitionname` to be at the first position
			if ($element1->nodeName === 'definitionname') {
				return -1;
			}
			else if ($element2->nodeName === 'definitionname') {
				return 1;
			}
			// force `name` to be at the second position
			else if ($element1->nodeName === 'name') {
				return -1;
			}
			else if ($element2->nodeName === 'name') {
				return 1;
			}
			// force `classname` to be at the third position
			else if ($element1->nodeName === 'classname') {
				return -1;
			}
			else if ($element2->nodeName === 'classname') {
				return 1;
			}
			else {
				// the rest is sorted by node name
				return strcmp($element1->nodeName, $element2->nodeName);
			}
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
		$type = $document->createElement('type');
		foreach ($form->getData()['data'] as $key => $value) {
			if ($value !== '') {
				if (is_string($value)) {
					$type->appendChild($document->createElement($key, $this->getAutoCdataValue($value)));
				}
				else {
					$type->appendChild($document->createElement($key, (string) $value));
				}
			}
		}
		
		$document->getElementsByTagName('import')->item(0)->appendChild($type);
		
		return $type;
	}
	
	/**
	 * Adds all condition specific fields to the given form container.
	 * 
	 * @param	IFormContainer		$dataContainer
	 * @param	bool			$addConditionObject
	 * @param	bool			$addConditionGroup
	 * @since	3.2
	 */
	protected function addConditionFields(IFormContainer $dataContainer, bool $addConditionObject = true, bool $addConditionGroup = true) {
		$prefix = preg_replace('~Fields$~', '', $dataContainer->getId());
		
		if ($addConditionObject) {
			$dataContainer->appendChild(
				TextFormField::create($prefix . 'ConditionObject')
					->attribute('data-tag', 'conditionobject')
					->label('wcf.acp.pip.objectType.condition.conditionObject')
					->description('wcf.acp.pip.objectType.condition.conditionObject.description')
					->required()
					->addValidator($this->getObjectTypeAlikeValueValidator('condition.conditionObject'))
			);
		}
		
		if ($addConditionGroup) {
			$dataContainer->appendChild(
				TextFormField::create($prefix . 'ConditionGroup')
					->attribute('data-tag', 'conditiongroup')
					->label('wcf.acp.pip.objectType.condition.conditionGroup')
					->description('wcf.acp.pip.objectType.condition.conditionGroup.description')
					->addValidator(new FormFieldValidator('format', function(TextFormField $formField) {
						if ($formField->getValue() && !preg_match('~^[a-z][A-z]+$~', $formField->getValue())) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'format',
									'wcf.acp.pip.objectType.condition.conditionGroup.error.format'
								)
							);
						}
					}))
			);
		}
		
		// classes extending `AbstractIntegerCondition`
		$integerConditions = [];
		foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
			$conditionDir = $application->getPackage()->getAbsolutePackageDir() . 'lib/system/condition/';
			
			if (file_exists($conditionDir)) {
				$directory = DirectoryUtil::getInstance($conditionDir);
				$conditionList = $directory->getFiles(SORT_ASC, new Regex('Condition\.class\.php$'));
				
				/** @var string $condition */
				foreach ($conditionList as $condition) {
					$pathPieces = explode('/', str_replace($conditionDir, '', $condition));
					$filename = array_pop($pathPieces);
					
					$className = $application->getAbbreviation() . '\system\condition\\';
					if (!empty($pathPieces)) {
						$className .= implode('\\', $pathPieces) . '\\';
					}
					$className .= basename($filename, '.class.php');
					if (class_exists($className) && is_subclass_of($className, AbstractIntegerCondition::class)) {
						$reflection = new \ReflectionClass($className);
						if ($reflection->isInstantiable()) {
							$integerConditions[] = $className;
						}
					}
				}
			}
		}
		
		/** @var TextFormField $className */
		$className = $dataContainer->getDocument()->getNodeById('className');
		
		$dataContainer->appendChildren([
			IntegerFormField::create($prefix . 'IntegerMinValue')
				->attribute('data-tag', 'minvalue')
				->label('wcf.acp.pip.objectType.condition.integer.minValue')
				->description('wcf.acp.pip.objectType.condition.integer.minValue.description')
				->addDependency(
					ValueFormFieldDependency::create('className')
						->field($className)
						->values($integerConditions)
				),
			IntegerFormField::create($prefix . 'IntegerMaxValue')
				->attribute('data-tag', 'maxvalue')
				->label('wcf.acp.pip.objectType.condition.integer.maxValue')
				->description('wcf.acp.pip.objectType.condition.integer.maxValue.description')
				->addDependency(
					ValueFormFieldDependency::create('className')
						->field($className)
						->values($integerConditions)
				)
		]);
		
		// `UserGroupCondition`
		$dataContainer->appendChild(
			BooleanFormField::create($prefix . 'UserGroupIncludeGuests')
				->attribute('data-tag', 'includeguests')
				->label('wcf.acp.pip.objectType.condition.userGroup.includeGuests')
				->description('wcf.acp.pip.objectType.condition.userGroup.includeGuests.description')
				->addDependency(
					ValueFormFieldDependency::create('className')
						->field($className)
						->values([UserGroupCondition::class])
				)
		);
		
		// `UserIntegerPropertyCondition`
		$dataContainer->appendChild(
			TextFormField::create($prefix . 'UserIntegerPropertyName')
				->attribute('data-tag', 'propertyname')
				->label('wcf.acp.pip.objectType.condition.userIntegerProperty.propertyName')
				->description('wcf.acp.pip.objectType.condition.userIntegerProperty.propertyName.description')
				->addDependency(
					ValueFormFieldDependency::create('className')
						->field($className)
						->values([UserIntegerPropertyCondition::class])
				)
				->addValidator(new FormFieldValidator('userTableIntegerColumn', function(TextFormField $formField) {
					$columns = WCF::getDB()->getEditor()->getColumns('wcf' . WCF_N . '_user');
					
					foreach ($columns as $column) {
						if ($column['name'] === $formField->getValue()) {
							if ($column['data']['type'] !== 'int') {
								$formField->addValidationError(new FormFieldValidationError(
									'noIntegerColumn',
									'wcf.acp.pip.objectType.condition.userIntegerProperty.propertyName.error.noIntegerColumn'
								));
							}
							
							return;
						}
					}
					
					$formField->addValidationError(new FormFieldValidationError(
						'nonExistent',
						'wcf.acp.pip.objectType.condition.userIntegerProperty.propertyName.error.nonExistent'
					));
				}))
		);
		
		// `UserTimestampPropertyCondition`
		$dataContainer->appendChild(
			TextFormField::create($prefix . 'UserTimestampPropertyName')
				->attribute('data-tag', 'propertyname')
				->label('wcf.acp.pip.objectType.condition.userTimestampProperty.propertyName')
				->description('wcf.acp.pip.objectType.condition.userTimestampProperty.propertyName.description')
				->addDependency(
					ValueFormFieldDependency::create('className')
						->field($className)
						->values([UserTimestampPropertyCondition::class])
				)
				->addValidator(new FormFieldValidator('userTableIntegerColumn', function(TextFormField $formField) {
					$columns = WCF::getDB()->getEditor()->getColumns('wcf' . WCF_N . '_user');
					
					foreach ($columns as $column) {
						if ($column['name'] === $formField->getValue()) {
							if ($column['data']['type'] !== 'int') {
								$formField->addValidationError(new FormFieldValidationError(
									'noIntegerColumn',
									'wcf.acp.pip.objectType.condition.userTimestampProperty.propertyName.error.noIntegerColumn'
								));
							}
							
							return;
						}
					}
					
					$formField->addValidationError(new FormFieldValidationError(
						'nonExistent',
						'wcf.acp.pip.objectType.condition.userTimestampProperty.propertyName.error.nonExistent'
					));
				}))
		);
		
		$parameters = ['dataContainer' => $dataContainer];
		EventHandler::getInstance()->fireAction($this, 'addConditionFields', $parameters);
	}
}
