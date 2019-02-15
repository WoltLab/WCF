<?php
namespace wcf\acp\form;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\devtools\project\DevtoolsProjectExcludedPackagesFormField;
use wcf\system\form\builder\field\devtools\project\DevtoolsProjectInstructionsFormField;
use wcf\system\form\builder\field\devtools\project\DevtoolsProjectOptionalPackagesFormField;
use wcf\system\form\builder\field\devtools\project\DevtoolsProjectRequiredPackagesFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\language\LanguageFactory;

/**
 * Shows the devtools project edit form.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.1
 */
class DevtoolsProjectEditForm extends DevtoolsProjectAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.devtools.project.list';
	
	/**
	 * @inheritDoc
	 */
	public $formAction = 'edit';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) {
			$this->formObject = new DevtoolsProject($_REQUEST['id']);
			if (!$this->formObject->projectID) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 * @since	5.2
	 */
	protected function setFormObjectData() {
		parent::setFormObjectData();
		
		// set additional data based on `package.xml` file
		$packageArchive = $this->formObject->getPackageArchive();
		
		/** @var TextFormField $packageIdentifier */
		$packageIdentifier = $this->form->getNodeById('packageIdentifier');
		$packageIdentifier->value($packageArchive->getPackageInfo('name'));
		
		/** @var TextFormField $packageName */
		$packageName = $this->form->getNodeById('packageName');
		$xmlPackageNames = $packageArchive->getPackageInfo('packageName');
		if (count($xmlPackageNames) === 1) {
			$packageName->value(reset($xmlPackageNames));
		}
		else {
			$packageNames = [];
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$packageNames[$language->languageID] = '';
				
				if (isset($xmlPackageNames[$language->languageCode])) {
					$packageNames[$language->languageID] = $xmlPackageNames[$language->languageCode];
				}
				else if (isset($xmlPackageNames['default'])) {
					$packageNames[$language->languageID] = $xmlPackageNames['default'];
				}
			}
			
			$packageName->value($packageNames);
		}
		
		/** @var TextFormField $packageDescription */
		$packageDescription = $this->form->getNodeById('packageDescription');
		$xmlPackageDescriptions = $packageArchive->getPackageInfo('packageDescription');
		if (count($xmlPackageDescriptions) === 1) {
			$packageDescription->value(reset($xmlPackageDescriptions));
		}
		else {
			$packageDescriptions = [];
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$packageDescriptions[$language->languageID] = '';
				
				if (isset($xmlPackageDescriptions[$language->languageCode])) {
					$packageDescriptions[$language->languageID] = $xmlPackageDescriptions[$language->languageCode];
				} else if (isset($xmlPackageDescriptions['default'])) {
					$packageDescriptions[$language->languageID] = $xmlPackageDescriptions['default'];
				}
			}
			
			$packageDescription->value($packageDescriptions);
		}
		
		if (!empty($packageArchive->getPackageInfo('isApplication'))) {
			/** @var BooleanFormField $isApplication */
			$isApplication = $this->form->getNodeById('isApplication');
			$isApplication->value(1);
		}
		
		if ($packageArchive->getPackageInfo('applicationDirectory') !== null) {
			/** @var TextFormField $applicationDirectory */
			$applicationDirectory = $this->form->getNodeById('applicationDirectory');
			$applicationDirectory->value($packageArchive->getPackageInfo('applicationDirectory'));
		}
		
		/** @var TextFormField $version */
		$version = $this->form->getNodeById('version');
		$version->value($packageArchive->getPackageInfo('version'));
		
		/** @var TextFormField $date */
		$date = $this->form->getNodeById('date');
		$date->value(date('Y-m-d', $packageArchive->getPackageInfo('date')));
		
		if ($packageArchive->getPackageInfo('packageurl') !== null) {
			/** @var TextFormField $packageUrl */
			$packageUrl = $this->form->getNodeById('packageurl');
			$packageUrl->value($packageArchive->getPackageInfo('packageurl'));
		}
		
		/** @var TextFormField $author */
		$author = $this->form->getNodeById('author');
		$author->value($packageArchive->getAuthorInfo('author'));
		
		if ($packageArchive->getAuthorInfo('authorURL') !== null) {
			/** @var TextFormField $authorUrl */
			$authorUrl = $this->form->getNodeById('authorUrl');
			$authorUrl->value($packageArchive->getAuthorInfo('authorURL'));
		}
		
		/** @var MultipleSelectionFormField $apiVersions */
		$apiVersions = $this->form->getNodeById('apiVersions');
		$apiVersions->value($packageArchive->getCompatibleVersions());
		
		$requirements = $packageArchive->getRequirements();
		if (!empty($requirements)) {
			$requirementData = [];
			foreach ($requirements as $optional) {
				$requirementData[] = [
					'file' => isset($optional['file']) ? 1 : 0,
					'minVersion' => $optional['minversion'] ?? '',
					'packageIdentifier' => $optional['name']
				];
			}
			
			/** @var DevtoolsProjectRequiredPackagesFormField $requiredPackages */
			$requiredPackages = $this->form->getNodeById('requiredPackages');
			$requiredPackages->value($requirementData);
		}
		
		$exclusions = $packageArchive->getExcludedPackages();
		if (!empty($exclusions)) {
			$exclusionData = [];
			foreach ($exclusions as $exclusion) {
				$exclusionData[] = [
					'packageIdentifier' => $exclusion['name'],
					'version' => $exclusion['version'] ?? ''
				];
			}
			
			/** @var DevtoolsProjectExcludedPackagesFormField $excludedPackages */
			$excludedPackages = $this->form->getNodeById('excludedPackages');
			$excludedPackages->value($exclusionData);
		}
		
		$optionals = $packageArchive->getOptionals();
		if (!empty($optionals)) {
			$exclusionData = [];
			foreach ($optionals as $optional) {
				$exclusionData[] = [
					'packageIdentifier' => $optional['name']
				];
			}
			
			/** @var DevtoolsProjectOptionalPackagesFormField $optionalPackages */
			$optionalPackages = $this->form->getNodeById('optionalPackages');
			$optionalPackages->value($exclusionData);
		}
		
		$installationInstructions = [];
		foreach ($packageArchive->getInstallInstructions() as $instruction) {
			$installationInstructions[] = [
				'application' => $instruction['attributes']['application'] ?? '',
				'runStandalone' => isset($instruction['attributes']['run']) && $instruction['attributes']['run'] === 'standalone' ? 1 : 0,
				'pip' => $instruction['pip'],
				'value' => $instruction['value']
			];
		}
		$instructions = [
			[
				'instructions' => $installationInstructions,
				'type' => 'install'
			]
		];
		
		foreach ($packageArchive->getUpdateInstructions() as $fromVersion => $updateInstructions) {
			$versionUpdateInstructions = [];
			
			foreach ($updateInstructions as $instruction) {
				$versionUpdateInstructions[] = [
					'application' => $instruction['attributes']['application'] ?? '',
					'runStandalone' => isset($instruction['attributes']['run']) && $instruction['attributes']['run'] === 'standalone' ? 1 : 0,
					'pip' => $instruction['pip'],
					'value' => $instruction['value']
				];
			}
			
			$instructions[] = [
				'fromVersion' => $fromVersion,
				'instructions' => $versionUpdateInstructions,
				'type' => 'update'
			];
		}
		
		/** @var DevtoolsProjectInstructionsFormField $instructionsField */
		$instructionsField = $this->form->getNodeById('instructions');
		$instructionsField->value($instructions);
	}
}
