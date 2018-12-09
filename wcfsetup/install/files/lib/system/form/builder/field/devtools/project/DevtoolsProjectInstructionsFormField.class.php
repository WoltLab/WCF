<?php
namespace wcf\system\form\builder\field\devtools\project;
use wcf\data\application\Application;
use wcf\data\package\installation\plugin\PackageInstallationPlugin;
use wcf\data\package\installation\plugin\PackageInstallationPluginList;
use wcf\data\package\Package;
use wcf\system\application\ApplicationHandler;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Form field implementation for the instructions of a devtools project.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Devtools\Project
 * @since	3.2
 */
class DevtoolsProjectInstructionsFormField extends AbstractFormField {
	use TDefaultIdFormField;
	
	/**
	 * list of available applications
	 * @var	null|Application[]
	 */
	protected $applications;
	
	/**
	 * list of available package installation plugins
	 * @var	null|PackageInstallationPlugin[]
	 */
	protected $packageInstallationPlugins = null;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__devtoolsProjectInstructionsFormField';
	
	/**
	 * @inheritDoc
	 */
	protected $__value = [];
	
	/**
	 * names of package installation plugins that support the `application`
	 * attribute
	 * @var	string[]
	 */
	protected static $applicationPips = [
		'acpTemplate',
		'file',
		'script',
		'template'
	];
	
	/**
	 * Returns the applications for which file-based package installation plugins
	 * can deliver files.
	 * 
	 * @return	Application[]
	 */
	public function getApplications() {
		if ($this->applications === null) {
			$this->applications = [];
			foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
				$this->applications[$application->getAbbreviation()] = $application;
			}
			
			uasort($this->applications, function(Application $app1, Application $app2) {
				return $app1->getAbbreviation() <=> $app2->getAbbreviation();
			});
		}
		
		return $this->applications;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtmlVariables() {
		return [
			'apps' => $this->getApplications(),
			'packageInstallationPlugins' => $this->getPackageInstallationPlugins()
		];
	}
	
	/**
	 * Returns the package installation plugins that can be used for instructions.
	 * 
	 * @return	PackageInstallationPlugin[]
	 */
	public function getPackageInstallationPlugins() {
		if ($this->packageInstallationPlugins === null) {
			$packageInstallationPluginList = new PackageInstallationPluginList();
			$packageInstallationPluginList->sqlOrderBy = 'pluginName ASC';
			$packageInstallationPluginList->readObjects();
			
			foreach ($packageInstallationPluginList as $packageInstallationPlugin) {
				$this->packageInstallationPlugins[$packageInstallationPlugin->pluginName] = $packageInstallationPlugin;
			}
		}
		
		return $this->packageInstallationPlugins;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId()) && is_array($this->getDocument()->getRequestData($this->getPrefixedId()))) {
			$this->__value = $this->getDocument()->getRequestData($this->getPrefixedId());
		}
		else {
			$this->__value = [];
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		// everything is already validated by JavaScript thus we skip
		// reporting specific errors and simply remove manipulated values
		$this->value(array_filter($this->getValue(), function($instructions) {
			if (!is_array($instructions)) {
				return false;
			}
			
			// ensure that all relevant elements are present
			if (!isset($instructions['type'])) {
				return false;
			}
			if (!isset($instructions['instructions'])) {
				$instructions['instructions'] = [];
			}
			if (!is_array($instructions['instructions'])) {
				return false;
			}
			
			if ($instructions['type'] !== 'install' && $instructions['type'] !== 'update') {
				return false;
			}
			
			if ($instructions['type'] === 'update') {
				if (!isset($instructions['fromVersion'])) {
					return false;
				}
				
				if (strpos($instructions['fromVersion'], '*') !== false) {
					if (!Package::isValidVersion(str_replace('*', '0', $instructions['fromVersion']))) {
						return false;
					}
				}
				else if (!Package::isValidVersion($instructions['fromVersion'])) {
					return false;
				}
			}
			
			foreach ($instructions['instructions'] as $instruction) {
				if (!isset($instruction['pip']) || !isset($this->getPackageInstallationPlugins()[$instruction['pip']])) {
					return false;
				}
				
				if (!empty($instruction['application'])) {
					if (!isset($this->getApplications()[$instruction['application']])) {
						return false;
					}
					
					if (!in_array($instruction['pip'], static::$applicationPips)) {
						return false;
					}
				}
				
				$instruction['runStandalone'] = $instruction['runStandalone'] ?? 0;
				$instruction['value'] = $instruction['value'] ?? '';
			}
			
			return true;
		}));
		
		// the only thing left to validate is to ensure that there are
		// installation instructions
		$hasInstallInstructions = false;
		foreach ($this->getValue() as $instructions) {
			if ($instructions['type'] === 'install') {
				$hasInstallInstructions = true;
				break;
			}
		}
		
		if (!$hasInstallInstructions) {
			$this->addValidationError(
				new FormFieldValidationError(
					'noInstallationInstructions',
					'wcf.acp.devtools.project.instructions.error.noInstallationInstructions'
				)
			);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getDefaultId() {
		return 'instructions';
	}
}
