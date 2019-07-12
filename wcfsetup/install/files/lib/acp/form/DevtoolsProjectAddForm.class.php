<?php
namespace wcf\acp\form;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\devtools\project\DevtoolsProjectAction;
use wcf\data\devtools\project\DevtoolsProjectList;
use wcf\data\package\installation\plugin\PackageInstallationPlugin;
use wcf\data\package\installation\plugin\PackageInstallationPluginList;
use wcf\data\package\Package;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\devtools\package\DevtoolsPackageXmlWriter;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\container\TabMenuFormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\devtools\project\DevtoolsProjectExcludedPackagesFormField;
use wcf\system\form\builder\field\devtools\project\DevtoolsProjectInstructionsFormField;
use wcf\system\form\builder\field\devtools\project\DevtoolsProjectOptionalPackagesFormField;
use wcf\system\form\builder\field\devtools\project\DevtoolsProjectRequiredPackagesFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\UrlFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\validation\FormFieldValidatorUtil;
use wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;

/**
 * Shows the devtools project add form.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.1
 * 
 * @property	null|DevtoolsProject	$formObject
 */
class DevtoolsProjectAddForm extends AbstractFormBuilderForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.devtools.project.add';
	
	/**
	 * @inheritDoc
	 */
	public $formAction = 'create';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canInstallPackage'];
	
	/**
	 * @inheritDoc
	 */
	public $objectActionClass = DevtoolsProjectAction::class;
	
	/**
	 * newly added or edited project
	 * @var	DevtoolsProject
	 */
	public $project;
	
	/**
	 * ids of the fields containing object data
	 * @var	string[]
	 */
	public $projectFields = ['name', 'path'];
	
	/**
	 * @inheritDoc
	 */
	protected function createForm() {
		parent::createForm();
		
		$tabMenu = TabMenuFormContainer::create('project');
		$this->form->appendChild($tabMenu);
		
		$dataTab = TabFormContainer::create('dataTab');
		$dataTab->label('wcf.global.form.data');
		$tabMenu->appendChild($dataTab);
		
		$mode = RadioButtonFormField::create('mode')
			->label('wcf.acp.devtools.project.add.mode')
			->options(function() {
				$options = [
					'import' => 'wcf.acp.devtools.project.add.mode.import',
					'setup' => 'wcf.acp.devtools.project.add.mode.setup',
				];
				
				if ($this->formObject !== null) {
					$options['edit'] = 'wcf.acp.devtools.project.add.mode.edit';
				}
				
				return $options;
			})
			->immutable($this->formObject !== null)
			->value($this->formObject ? 'edit' : 'import');
		
		$dataContainer = FormContainer::create('data')
			->label('wcf.global.form.data')
			->appendChildren([
				$mode,
				TextFormField::create('name')
					->label('wcf.acp.devtools.project.name')
					->required()
					->addValidator(new FormFieldValidator('uniqueness', function (TextFormField $formField) {
						$name = $formField->getSaveValue();
						
						if ($this->formObject === null || $this->formObject->name !== $name) {
							$projectList = new DevtoolsProjectList();
							$projectList->getConditionBuilder()->add('name = ?', [$name]);
							
							if ($projectList->countObjects() > 0) {
								$formField->addValidationError(
									new FormFieldValidationError(
										'notUnique',
										'wcf.acp.devtools.project.name.error.notUnique'
									)
								);
							}
						}
					})),
				
				TextFormField::create('path')
					->label('wcf.acp.devtools.project.path')
					->required()
					->addValidator(new FormFieldValidator('validPath', function (TextFormField $formField) {
						// ensure that unified directory separators are used
						// and that there is a trailing slash
						$formField->value(
							FileUtil::addTrailingSlash(
								FileUtil::unifyDirSeparator($formField->getSaveValue() ?? '')
							)
						);
						
						$path = $formField->getSaveValue();
						
						/** @var RadioButtonFormField $modeField */
						$modeField = $formField->getDocument()->getNodeById('mode');
						
						switch ($modeField->getSaveValue()) {
							case 'import':
							case 'edit':
								if ($this->formObject === null || $this->formObject->path !== $path) {
									$errorType = DevtoolsProject::validatePath($path);
									if ($errorType !== '') {
										$formField->addValidationError(
											new FormFieldValidationError(
												$errorType,
												'wcf.acp.devtools.project.path.error.' . $errorType
											)
										);
									}
								}
								
								break;
							
							case 'setup':
								if (is_dir($path)) {
									$formField->addValidationError(
										new FormFieldValidationError(
											'pathExists',
											'wcf.acp.devtools.project.path.error.pathExists'
										)
									);
								}
								else if (!is_dir(dirname($path))) {
									$formField->addValidationError(
										new FormFieldValidationError(
											'parentDoesNotExist',
											'wcf.acp.devtools.project.path.error.parentDoesNotExist'
										)
									);
								}
								else {
									if (!FileUtil::makePath($path)) {
										$formField->addValidationError(
											new FormFieldValidationError(
												'cannotMakeDirectory',
												'wcf.acp.devtools.project.path.error.cannotMakeDirectory'
											)
										);
									}
									else {
										// remove directory for now again
										rmdir($path);
									}
								}
								
								break;
							
							default:
								throw new \LogicException("Unknown mode '{$modeField->getSaveValue()}'.");
								break;
						}
					}))
					->addValidator(new FormFieldValidator('uniqueness', function (TextFormField $formField) {
						$path = $formField->getSaveValue();
						
						if ($this->formObject === null || $this->formObject->path !== $path) {
							$projectList = new DevtoolsProjectList();
							$projectList->getConditionBuilder()->add('path = ?', [$path]);
							
							if ($projectList->countObjects() > 0) {
								$formField->addValidationError(
									new FormFieldValidationError(
										'notUnique',
										'wcf.acp.devtools.project.path.error.notUnique'
									)
								);
							}
						}
					}))
			]);
		$dataTab->appendChild($dataContainer);
		
		$packageInformation = FormContainer::create('packageInformation')
			->label('wcf.acp.devtools.project.packageInformation')
			->appendChildren([
				TextFormField::create('packageIdentifier')
					->label('wcf.acp.devtools.project.packageIdentifier')
					->description('wcf.acp.devtools.project.packageIdentifier.description')
					->required()
					->maximumLength(191)
					->addValidator(new FormFieldValidator('format', function (TextFormField $formField) {
						if (!Package::isValidPackageName($formField->getSaveValue())) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'format',
									'wcf.acp.devtools.project.packageIdentifier.error.format'
								)
							);
						}
					})),
				
				TextFormField::create('packageName')
					->label('wcf.acp.devtools.project.packageName')
					->required()
					->maximumLength(255)
					->i18n()
					->languageItemPattern('__NONE__'),
				
				TextFormField::create('packageDescription')
					->label('wcf.global.description')
					->required()
					->maximumLength(255)
					->i18n()
					->languageItemPattern('__NONE__'),
				
				BooleanFormField::create('isApplication')
					->label('wcf.acp.devtools.project.isApplication')
					->description('wcf.acp.devtools.project.isApplication.description'),
				
				TextFormField::create('applicationDirectory')
					->label('wcf.acp.devtools.project.applicationDirectory')
					->description('wcf.acp.devtools.project.applicationDirectory.description')
					->available($this->formObject === null || !$this->formObject->isCore())
					->addValidator(FormFieldValidatorUtil::getRegularExpressionValidator(
						'[A-z0-9\-\_]+$',
						'wcf.acp.devtools.project.applicationDirectory'
					)),
				
				TextFormField::create('version')
					->label('wcf.acp.devtools.project.packageVersion')
					->description('wcf.acp.devtools.project.packageVersion.description')
					->required()
					->maximumLength(255),
				
				DateFormField::create('date')
					->label('wcf.acp.devtools.project.packageDate')
					->description('wcf.acp.devtools.project.packageDate.description')
					->required()
					->saveValueFormat('Y-m-d'),
				
				UrlFormField::create('packageUrl')
					->label('wcf.acp.devtools.project.packageUrl')
					->description('wcf.acp.devtools.project.packageUrl.description')
					->maximumLength(255)
			])
			->addDependency(
				ValueFormFieldDependency::create('mode')
					->field($mode)
					->values(['edit', 'setup'])
			);
		$dataTab->appendChild($packageInformation);
		
		/** @var BooleanFormField $isApplication */
		$isApplication = $packageInformation->getNodeById('isApplication');
		$packageInformation->getNodeById('applicationDirectory')
			->addDependency(
				NonEmptyFormFieldDependency::create('isApplication')
					->field($isApplication)
			);
		
		$authorInformation = FormContainer::create('authorInformation')
			->label('wcf.acp.devtools.project.authorInformation')
			->appendChildren([
				TextFormField::create('author')
					->label('wcf.acp.devtools.project.author')
					->required()
					->maximumLength(255),
				
				UrlFormField::create('authorUrl')
					->label('wcf.acp.devtools.project.authorUrl')
					->maximumLength(255)
			])
			->addDependency(
				ValueFormFieldDependency::create('mode')
					->field($mode)
					->values(['edit', 'setup'])
			);
		$dataTab->appendChild($authorInformation);
		
		$compatibility = FormContainer::create('compatibility')
			->label('wcf.acp.devtools.project.compatibility')
			->appendChildren([
				MultipleSelectionFormField::create('apiVersions')
					->label('wcf.acp.devtools.project.apiVersions')
					->description('wcf.acp.devtools.project.apiVersions.description')
					->options(function () {
						$apiVersions = array_filter(array_merge(WCF::getSupportedLegacyApiVersions(), [WSC_API_VERSION]), function($value) {
							return $value !== 2017;
						});
						
						sort($apiVersions);
						
						return array_combine($apiVersions, $apiVersions);
					})
					->available($this->formObject === null || !$this->formObject->isCore())
			])
			->addDependency(
				ValueFormFieldDependency::create('mode')
					->field($mode)
					->values(['edit', 'setup'])
			);
		$dataTab->appendChild($compatibility);
		
		$requiredPackages = FormContainer::create('requiredPackagesContainer')
			->label('wcf.acp.devtools.project.requiredPackages')
			->description('wcf.acp.devtools.project.requiredPackages.description')
			->appendChild(
				DevtoolsProjectRequiredPackagesFormField::create()
					->addValidator(new FormFieldValidator('selfRequirement', function(DevtoolsProjectRequiredPackagesFormField $formField) {
						/** @var TextFormField $packageIdentifier */
						$packageIdentifier = $formField->getDocument()->getNodeById('packageIdentifier');
						
						// ensure that the package does not require itself
						foreach ($formField->getSaveValue() as $requirement) {
							if ($requirement['packageIdentifier'] === $packageIdentifier->getSaveValue()) {
								$formField->addValidationError(
									new FormFieldValidationError(
										'selfRequirement',
										'wcf.acp.devtools.project.requiredPackage.error.selfRequirement'
									)
								);
							}
						}
					}))
					->addValidator(new FormFieldValidator('missingFiles', function(DevtoolsProjectRequiredPackagesFormField $formField) {
						/** @var TextFormField $pathField */
						$pathField = $this->form->getNodeById('path');
						$path = FileUtil::addTrailingSlash($pathField->getSaveValue());
						
						$missingFiles = [];
						foreach ($formField->getSaveValue() as $requirement) {
							if ($requirement['file'] && !is_file($path . "requirements/{$requirement['packageIdentifier']}.tar")) {
								$missingFiles[] = "requirements/{$requirement['packageIdentifier']}.tar";
							}
						}
						
						if (!empty($missingFiles)) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'missingFiles',
									'wcf.acp.devtools.project.requiredPackage.error.missingFiles',
									['missingFiles' => $missingFiles]
								)
							);
						}
					}))
			);
		$tabMenu->appendChild(
			TabFormContainer::create('requiredPackagesTab')
				->label('wcf.acp.devtools.project.requiredPackages.shortTitle')
				->appendChild($requiredPackages)
				->addDependency(
					ValueFormFieldDependency::create('mode')
						->field($mode)
						->values(['edit', 'setup'])
				)
		);
		
		$optionalPackages = FormContainer::create('optionalPackagesContainer')
			->label('wcf.acp.devtools.project.optionalPackages')
			->description('wcf.acp.devtools.project.optionalPackages.description')
			->appendChild(
				DevtoolsProjectOptionalPackagesFormField::create()
					->addValidator(new FormFieldValidator('selfOptional', function (DevtoolsProjectOptionalPackagesFormField $formField) {
						/** @var TextFormField $packageIdentifier */
						$packageIdentifier = $formField->getDocument()->getNodeById('packageIdentifier');
						
						// ensure that the package does not mark itself as optional
						foreach ($formField->getSaveValue() as $requirement) {
							if ($requirement['packageIdentifier'] === $packageIdentifier->getSaveValue()) {
								$formField->addValidationError(
									new FormFieldValidationError(
										'selfExclusion',
										'wcf.acp.devtools.project.optionalPackage.error.selfOptional'
									)
								);
							}
						}
					}))
					->addValidator(new FormFieldValidator('requirementOptional', function (DevtoolsProjectOptionalPackagesFormField $formField) {
						/** @var DevtoolsProjectRequiredPackagesFormField $requiredPackagesField */
						$requiredPackagesField = $formField->getDocument()->getNodeById('requiredPackages');
						$requiredPackages = [];
						foreach ($requiredPackagesField->getSaveValue() as $requiredPackage) {
							$requiredPackages[$requiredPackage['packageIdentifier']] = $requiredPackage;
						}
						
						// ensure that the optionals and requirements do not conflict
						foreach ($formField->getSaveValue() as $optional) {
							if (isset($requiredPackages[$optional['packageIdentifier']])) {
								$erroneousPackages[] = $optional['packageIdentifier'];
							}
						}
						
						if (!empty($erroneousPackages)) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'requirementOptional',
									'wcf.acp.devtools.project.optionalPackage.error.requirementOptional',
									['affectedPackages' => $erroneousPackages]
								)
							);
						}
					}))
					->addValidator(new FormFieldValidator('exclusionOptional', function(DevtoolsProjectOptionalPackagesFormField $formField) {
						/** @var DevtoolsProjectExcludedPackagesFormField $excludedPackagesField */
						$excludedPackagesField = $formField->getDocument()->getNodeById('excludedPackages');
						$excludedPackages = [];
						foreach ($excludedPackagesField->getSaveValue() as $requiredPackage) {
							$excludedPackages[$requiredPackage['packageIdentifier']] = $requiredPackage;
						}
						
						// ensure that the exclusions and requirements do not conflict
						foreach ($formField->getSaveValue() as $optional) {
							if (isset($excludedPackages[$optional['packageIdentifier']])) {
								$erroneousPackages[] = $optional['packageIdentifier'];
							}
						}
						
						if (!empty($erroneousPackages)) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'requirementOptional',
									'wcf.acp.devtools.project.optionalPackage.error.exclusionOptional',
									['affectedPackages' => $erroneousPackages]
								)
							);
						}
					}))
					->addValidator(new FormFieldValidator('missingFiles', function(DevtoolsProjectOptionalPackagesFormField $formField) {
						/** @var TextFormField $pathField */
						$pathField = $this->form->getNodeById('path');
						$path = FileUtil::addTrailingSlash($pathField->getSaveValue());
						
						$missingFiles = [];
						foreach ($formField->getSaveValue() as $optional) {
							if (!is_file($path . "optionals/{$optional['packageIdentifier']}.tar")) {
								$missingFiles[] = "optionals/{$optional['packageIdentifier']}.tar";
							}
						}
						
						if (!empty($missingFiles)) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'missingFiles',
									'wcf.acp.devtools.project.optionalPackage.error.missingFiles',
									['missingFiles' => $missingFiles]
								)
							);
						}
					}))
			);
		$tabMenu->appendChild(
			TabFormContainer::create('optionalPackagesTab')
				->label('wcf.acp.devtools.project.optionalPackages.shortTitle')
				->appendChild($optionalPackages)
				->addDependency(
					ValueFormFieldDependency::create('mode')
						->field($mode)
						->values(['edit'])
				)
		);
		
		$excludedPackages = FormContainer::create('excludedPackagesContainer')
			->label('wcf.acp.devtools.project.excludedPackages')
			->description('wcf.acp.devtools.project.excludedPackages.description')
			->appendChild(
				DevtoolsProjectExcludedPackagesFormField::create()
					->addValidator(new FormFieldValidator('selfExclusion', function (DevtoolsProjectExcludedPackagesFormField $formField) {
						/** @var TextFormField $packageIdentifier */
						$packageIdentifier = $formField->getDocument()->getNodeById('packageIdentifier');
						
						// ensure that the package does not exclude itself
						foreach ($formField->getSaveValue() as $requirement) {
							if ($requirement['packageIdentifier'] === $packageIdentifier->getSaveValue()) {
								$formField->addValidationError(
									new FormFieldValidationError(
										'selfExclusion',
										'wcf.acp.devtools.project.excludedPackage.error.selfExclusion'
									)
								);
							}
						}
					}))
					->addValidator(new FormFieldValidator('requirementExclusion', function (DevtoolsProjectExcludedPackagesFormField $formField) {
						/** @var DevtoolsProjectRequiredPackagesFormField $requiredPackagesField */
						$requiredPackagesField = $formField->getDocument()->getNodeById('requiredPackages');
						$requiredPackageVersions = [];
						foreach ($requiredPackagesField->getSaveValue() as $requiredPackage) {
							$requiredPackageVersions[$requiredPackage['packageIdentifier']] = $requiredPackage['minVersion'];
						}
						
						// ensure that the exclusions and requirements do not conflict
						$affectedPackages = [];
						foreach ($formField->getSaveValue() as $exclusion) {
							if (isset($requiredPackageVersions[$exclusion['packageIdentifier']])) {
								$requiredVersion = $requiredPackageVersions[$exclusion['packageIdentifier']];
								$excludedVersion = $exclusion['version'];
								
								// we enfore a hard rule: if a package is both an exclusion
								// and a requirement, both must specify a version
								if ($requiredVersion === '' || $excludedVersion === '') {
									$affectedPackages[] = $exclusion['packageIdentifier'];
								}
								else if (Package::compareVersion($excludedVersion, $requiredVersion) <= 0) {
									$affectedPackages[] = $exclusion['packageIdentifier'];
								}
							}
						}
						
						if (!empty($affectedPackages)) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'requirementExclusion',
									'wcf.acp.devtools.project.excludedPackage.error.requirementExclusion',
									['affectedPackages' => $affectedPackages]
								)
							);
						}
					}))
			);
		$tabMenu->appendChild(
			TabFormContainer::create('excludedPackagesTab')
				->label('wcf.acp.devtools.project.excludedPackages.shortTitle')
				->appendChild($excludedPackages)
				->addDependency(
					ValueFormFieldDependency::create('mode')
						->field($mode)
						->values(['edit', 'setup'])
				)
		);
		
		$instructions = FormContainer::create('instructionsContainer')
			->label('wcf.acp.devtools.project.instructions')
			->description('wcf.acp.devtools.project.instructions.description')
			->appendChild(
				DevtoolsProjectInstructionsFormField::create()
					->label('wcf.acp.devtools.project.instructions')
					->description('wcf.acp.devtools.project.instructions')
					->addValidator(new FormFieldValidator('updateFromPreviousVersion', function(DevtoolsProjectInstructionsFormField $formField) {
						/** @var TextFormField $versionField */
						$versionField = $this->form->getNodeById('version');
						$version = $versionField->getSaveValue();
						
						foreach ($formField->getValue() as $key => $instructions) {
							if ($instructions['type'] === 'install') {
								continue;
							}
							
							$fromVersion = $instructions['fromVersion'];
							if (strpos($fromVersion, '*') !== false) {
								// assume the smallest version by replacing
								// all wildcards with zeros
								$checkedFromVersion = str_replace('*', '0', $fromVersion);
								if (Package::compareVersion($version, $checkedFromVersion) <= 0) {
									$formField->addValidationError(
										new FormFieldValidationError(
											'updateForFutureVersion',
											'wcf.acp.devtools.project.instructions.type.update.error.updateForFutureVersion',
											[
												'fromVersion' => $fromVersion,
												'instructions' => $key,
												'version' => $version
											]
										)
									);
								}
							}
							else if (Package::compareVersion($version, $fromVersion) <= 0) {
								$formField->addValidationError(
									new FormFieldValidationError(
										'updateForFutureVersion',
										'wcf.acp.devtools.project.instructions.type.update.error.updateForFutureVersion',
										[
											'fromVersion' => $fromVersion,
											'instructions' => $key,
											'version' => $version
										]
									)
								);
							}
						}
					}))
					->addValidator($this->getInstructionValuesValidator())
			);
		
		$tabMenu->appendChild(
			TabFormContainer::create('instructionsTab')
				->label('wcf.acp.devtools.project.instructions')
				->appendChild($instructions)
				->addDependency(
					ValueFormFieldDependency::create('mode')
						->field($mode)
						->values(['edit'])
				)
		);
	}
	
	/**
	 * Returns the form field validator for the instructions form field to check
	 * the values of all instructions.
	 * 
	 * @return	FormFieldValidator
	 */
	protected function getInstructionValuesValidator() {
		return new FormFieldValidator('instructionValues', function(DevtoolsProjectInstructionsFormField $formField) {
			/** @var TextFormField $pathField */
			$pathField = $this->form->getNodeById('path');
			$path = FileUtil::addTrailingSlash($pathField->getSaveValue());
			
			/** @var TextFormField $packageIdentifierField */
			$packageIdentifierField = $this->form->getNodeById('packageIdentifier');
			$packageIdentifier = $packageIdentifierField->getSaveValue();
			
			/** @var BooleanFormField $isApplicationField */
			$isApplicationField = $this->form->getNodeById('isApplication');
			$isApplication = $isApplicationField->getSaveValue();
			
			$packageInstallationPluginList = new PackageInstallationPluginList();
			$packageInstallationPluginList->readObjects();
			
			/** @var PackageInstallationPlugin[] $packageInstallationPlugins */
			$packageInstallationPlugins = [];
			foreach ($packageInstallationPluginList as $packageInstallationPlugin) {
				$packageInstallationPlugins[$packageInstallationPlugin->pluginName] = $packageInstallationPlugin;
			}
			
			foreach ($formField->getValue() as $instructionsKey => $instructions) {
				if (empty($instructions['instructions'])) {
					$formField->addValidationError(
						new FormFieldValidationError(
							'missingInstructions',
							'wcf.acp.devtools.project.instructions.error.missingInstructions',
							['instructions' => $instructionsKey]
						)
					);
					
					continue;
				}
				
				foreach ($instructions['instructions'] as $instructionKey => $instruction) {
					$value = $instruction['value'];
					$packageInstallationPlugin = $packageInstallationPlugins[$instruction['pip']];
					
					// explicity set value for valiation if instruction value
					// is empty but supports default filename
					if ($value === '' && $packageInstallationPlugin->getDefaultFilename() !== null) {
						$value = $packageInstallationPlugin->getDefaultFilename();
					}
					
					switch ($instruction['pip']) {
						case 'acpTemplate':
						case 'file':
						case 'template':
							// core is too special, ignore it
							if ($this->formObject !== null && $this->formObject->isCore()) {
								break;
							}
							
							// only tar archives are supported for file-based pips
							if (substr($value, -4) !== '.tar') {
								$formField->addValidationError(
									new FormFieldValidationError(
										'noArchive',
										'wcf.acp.devtools.project.instruction.error.noArchive',
										[
											'instruction' => $instructionKey,
											'instructions' => $instructionsKey
										]
									)
								);
							}
							// the associated directory with the source fles
							// has to exist ...
							else if (!is_dir($path . substr($value, 0, -4))) {
								// ... unless it is an update and an archive
								// with updated files only
								if ($instructions['type'] === 'update' && preg_match('~^(.+)_update\.tar$~', $value, $match)) {
									if (!is_dir($path . $match[1])) {
										$formField->addValidationError(
											new FormFieldValidationError(
												'missingDirectoryForUpdatedFiles',
												'wcf.acp.devtools.project.instruction.error.missingDirectoryForUpdatedFiles',
												[
													'directory' => $path . $match[1] . '/',
													'instruction' => $instructionKey,
													'instructions' => $instructionsKey
												]
											)
										);
									}
								}
								else {
									$formField->addValidationError(
										new FormFieldValidationError(
											'missingDirectory',
											'wcf.acp.devtools.project.instruction.error.missingDirectory',
											[
												'directory' => $path . substr($value, 0, -4) . '/',
												'instruction' => $instructionKey,
												'instructions' => $instructionsKey
											]
										)
									);
								}
							}
							break;
						
						case 'language':
							if ($value === 'language/*.xml') {
								$directory = FileUtil::addTrailingSlash(dirname($path . $value));
								$directoryUtil = DirectoryUtil::getInstance($directory);
								if (empty($directoryUtil->getFiles(SORT_ASC, Regex::compile('.+\.xml')))) {
									$formField->addValidationError(
										new FormFieldValidationError(
											'missingFiles',
											'wcf.acp.devtools.project.instruction.language.error.missingFiles',
											[
												'directory' => $directory,
												'instruction' => $instructionKey,
												'instructions' => $instructionsKey
											]
										)
									);
								}
							}
							else if (substr($value, -4) !== '.xml') {
								$formField->addValidationError(
									new FormFieldValidationError(
										'noXmlFile',
										'wcf.acp.devtools.project.instruction.error.noXmlFile',
										[
											'instruction' => $instructionKey,
											'instructions' => $instructionsKey
										]
									)
								);
							}
							
							break;
						
						case 'script':
							// only PHP files are supported for file-based pips
							if (substr($value, -4) !== '.php') {
								$formField->addValidationError(
									new FormFieldValidationError(
										'noPhpFile',
										'wcf.acp.devtools.project.instruction.script.error.noPhpFile',
										[
											'instruction' => $instructionKey,
											'instructions' => $instructionsKey
										]
									)
								);
							}
							else {
								$application = 'wcf';
								if (!empty($instruction['application'])) {
									$application = $instruction['application'];
								}
								else if ($isApplication) {
									$application = Package::getAbbreviation($packageIdentifier);
								}
								
								$missingFile = true;
								$checkedFileLocations = [];
								if ($this->formObject !== null && $this->formObject->isCore()) {
									$scriptLocation = $path . 'wcfsetup/install/files/' . $instruction['value'];
									if (!is_file($scriptLocation)) {
										$checkedFileLocations[] = $scriptLocation;
									}
									else {
										$missingFile = false;
									}
								}
								else {
									// try to find matching `file` instruction
									// for determined application
									foreach ($instructions['instructions'] as $fileSearchInstruction) {
										if ($fileSearchInstruction['pip'] === 'file') {
											$fileSearchValue = $fileSearchInstruction['value'];
											
											// ignore empty instructions with default filename
											if ($fileSearchValue === '' && $packageInstallationPlugins['file']->getDefaultFilename() !== null) {
												$fileSearchValue = $packageInstallationPlugins['file']->getDefaultFilename();
											}
											
											$fileApplication = 'wcf';
											if (!empty($fileSearchInstruction['application'])) {
												$fileApplication = $fileSearchInstruction['application'];
											}
											else if ($isApplication) {
												$fileApplication = Package::getAbbreviation($packageIdentifier);
											}
											
											if ($fileApplication === $application) {
												$scriptLocation = $path . substr($fileSearchValue, 0, -4) . '/' . $instruction['value'];
												if (!is_file($scriptLocation)) {
													$checkedFileLocations[] = $scriptLocation;
												}
												else {
													$missingFile = false;
													break;
												}
											}
										}
									}
								}
								
								if ($missingFile) {
									$formField->addValidationError(
										new FormFieldValidationError(
											'missingFile',
											'wcf.acp.devtools.project.instruction.script.error.missingFile',
											[
												'checkedFileLocations' => $checkedFileLocations,
												'instruction' => $instructionKey,
												'instructions' => $instructionsKey
											]
										)
									);
								}
							}
							
							break;
						
						default:
							$filePath = $path . $value;
							if ($this->formObject !== null && $this->formObject->isCore()) {
								$filePath = $path . 'com.woltlab.wcf/' . $value;
							}
							
							if (!file_exists($filePath)) {
								$formField->addValidationError(
									new FormFieldValidationError(
										'missingFile',
										'wcf.acp.devtools.project.instruction.error.missingFile',
										[
											'file' => $filePath,
											'instruction' => $instructionKey,
											'instructions' => $instructionsKey
										]
									)
								);
							}
							else if (
								is_subclass_of($packageInstallationPlugin->className, AbstractXMLPackageInstallationPlugin::class) &&
								substr($value, -4) !== '.xml'
							) {
								$formField->addValidationError(
									new FormFieldValidationError(
										'noXmlFile',
										'wcf.acp.devtools.project.instruction.error.noXmlFile',
										[
											'instruction' => $instructionKey,
											'instructions' => $instructionsKey
										]
									)
								);
							}
							
							break;
					}
				}
			}
		});
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$data = $this->form->getData();
		$projectData = [];
		foreach ($this->projectFields as $projectField) {
			if (isset($data['data'][$projectField])) {
				$projectData[$projectField] = $data['data'][$projectField];
				unset($data['data'][$projectField]);
			}
		}
		
		$action = $this->formAction;
		if ($this->objectActionName) {
			$action = $this->objectActionName;
		}
		else if ($this->formAction === 'edit') {
			$action = 'update';
		}
		
		/** @var AbstractDatabaseObjectAction objectAction */
		$this->objectAction = new $this->objectActionClass(
			array_filter([$this->formObject]),
			$action,
			['data' => $projectData]
		);
		$project = $this->objectAction->executeAction()['returnValues'];
		
		if (!($project instanceof DevtoolsProject)) {
			if ($this->formObject instanceof DevtoolsProject) {
				$project = new DevtoolsProject($this->formObject->projectID);
			}
			else {
				throw new \LogicException('Cannot determine project object.');
			}
		}
		
		if ($data['data']['mode'] !== 'import') {
			$this->writePackageXml($project, $data);
		}
		
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * Writes the updated `package.xml` file for the given project using the given data.
	 *
	 * @param	DevtoolsProject		$project
	 * @param	array			$data
	 */
	protected function writePackageXml(DevtoolsProject $project, array $data) {
		$xmlData = array_merge($data, $data['data']);
		unset($xmlData['data'], $xmlData['mode']);
		$packageXmlWriter = new DevtoolsPackageXmlWriter($project, $xmlData);
		$packageXmlWriter->write();
	}
}
