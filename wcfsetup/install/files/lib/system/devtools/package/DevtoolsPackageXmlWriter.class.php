<?php
namespace wcf\system\devtools\package;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\system\language\LanguageFactory;
use wcf\util\XMLWriter;

/**
 * Writes the `package.xml` file of a project.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Package
 * @since	5.2
 */
class DevtoolsPackageXmlWriter {
	/**
	 * data used to write the `package.xml` file
	 * @var	array
	 */
	protected $packageXmlData;
	
	/**
	 * devtools project whose `package.xml` file will be written
	 * @var	DevtoolsProject
	 */
	protected $project;
	
	/**
	 * xml writer object
	 * @var	XMLWriter
	 */
	protected $xmlWriter;
	
	/**
	 * code name of current WSC version
	 */
	const WSC_CODENAME = 'tornado';
	
	/**
	 * Creates a new `DevtoolsPackageXmlWriter` object.
	 *
	 * @param	DevtoolsProject		$project
	 * @param	array			$packageXmlData
	 */
	public function __construct(DevtoolsProject $project, array $packageXmlData) {
		$this->project = $project;
		$this->packageXmlData = $packageXmlData;
	}
	
	/**
	 * Returns `true` if the given string needs to be placed in a CDATA
	 * section or `false`, otherwise.
	 * 
	 * @param	string		$string
	 * @return	boolean
	 */
	protected function requiresCdata($string) {
		return strpos($string, '<') !== false ||
			strpos($string, '>') !== false ||
			strpos($string, '&') !== false;
	}
	
	/**
	 * Writes the `package.xml` file.
	 */
	public function write() {
		$this->xmlWriter = new XMLWriter();
		$this->xmlWriter->beginDocument(
			'package',
			'http://www.woltlab.com',
			'http://www.woltlab.com/XSD/' . static::WSC_CODENAME . '/package.xsd',
			['name' => $this->packageXmlData['packageIdentifier']]
		);
		
		$this->writePackageInformation();
		$this->writeAuthorInformation();
		$this->writeRequiredPackages();
		$this->writeOptionalPackages();
		$this->writeExcludedPackages();
		$this->writeCompatibility();
		$this->writeInstructions();
		
		$this->xmlWriter->endDocument($this->project->getPackageXmlPath());
	}
	
	/**
	 * Writes the `authorinformation` element.
	 */
	protected function writeAuthorInformation() {
		$this->xmlWriter->startElement('authorinformation');
		
		$this->xmlWriter->writeElement(
			'author',
			$this->packageXmlData['author'],
			[],
			$this->requiresCdata($this->packageXmlData['author'])
		);
		if (isset($this->packageXmlData['authorUrl']) && $this->packageXmlData['authorUrl'] !== '') {
			$this->xmlWriter->writeElement(
				'authorurl',
				$this->packageXmlData['authorUrl'],
				[],
				$this->requiresCdata($this->packageXmlData['authorUrl'])
			);
		}
		
		$this->xmlWriter->endElement();
	}
	
	/**
	 * Writes the `compatibility` element.
	 */
	protected function writeCompatibility() {
		if (empty($this->packageXmlData['apiVersions'])) {
			return;
		}
		
		$this->xmlWriter->startElement('compatibility');
		
		foreach ($this->packageXmlData['apiVersions'] as $apiVersion) {
			$this->xmlWriter->writeElement('api', '', ['version' => $apiVersion]);
		}
		
		$this->xmlWriter->endElement();
	}
	
	/**
	 * Writes the `optionalpackages` element.
	 */
	protected function writeExcludedPackages() {
		if (!empty($this->packageXmlData['excludedPackages'])) {
			$this->xmlWriter->startElement('excludedpackages');
			
			foreach ($this->packageXmlData['excludedPackages'] as $excludedPackage) {
				$attributes = [];
				if (!empty($excludedPackage['version'])) {
					$attributes['version'] = $excludedPackage['version'];
				}
				
				$this->xmlWriter->writeElement(
					'excludedpackage',
					$excludedPackage['packageIdentifier'],
					$attributes,
					false
				);
			}
			
			$this->xmlWriter->endElement();
		}
	}
	
	/**
	 * Writes the `instructions` elements.
	 */
	protected function writeInstructions() {
		if (empty($this->packageXmlData['instructions'])) {
			return;
		}
		
		foreach ($this->packageXmlData['instructions'] as $instructions) {
			$attributes = ['type' => $instructions['type']];
			if ($instructions['type'] === 'update') {
				$attributes['fromversion'] = $instructions['fromVersion'];
			}
			
			$this->xmlWriter->startElement('instructions', $attributes);
			
			foreach ($instructions['instructions'] as $instruction) {
				$attributes = ['type' => $instruction['pip']];
				if (!empty($instruction['runStandalone'])) {
					$attributes['run'] = 'standalone';
				}
				if (!empty($instruction['application'])) {
					$attributes['application'] = $instruction['application'];
				}
				
				$this->xmlWriter->writeElement('instruction', $instruction['value'], $attributes, false);
			}
			
			$this->xmlWriter->endElement();
		}
	}
	
	/**
	 * Writes the `optionalpackages` element.
	 */
	protected function writeOptionalPackages() {
		if (!empty($this->packageXmlData['optionalPackages'])) {
			$this->xmlWriter->startElement('optionalpackages');
			
			foreach ($this->packageXmlData['optionalPackages'] as $optionalPackage) {
				$this->xmlWriter->writeElement(
					'optionalpackage',
					$optionalPackage['packageIdentifier'],
					['file' => "optionals/{$optionalPackage['packageIdentifier']}.tar"],
					false
				);
			}
			
			$this->xmlWriter->endElement();
		}
	}
	
	/**
	 * Writes the `packageinformation` element.
	 */
	protected function writePackageInformation() {
		$this->xmlWriter->startElement('packageinformation');
		
		$this->xmlWriter->writeComment(" {$this->packageXmlData['packageIdentifier']} ");
		
		$english = LanguageFactory::getInstance()->getLanguageByCode('en');
		
		if (isset($this->packageXmlData['packageName_i18n'])) {
			$defaultLanguageID = null;
			if ($english !== null && isset($this->packageXmlData['packageName_i18n'][$english->languageID])) {
				$defaultLanguageID = $english->languageID;
			}
			else {
				reset($this->packageXmlData['packageName_i18n']);
				$defaultLanguageID = key($this->packageXmlData['packageName_i18n']);
			}
			
			$this->xmlWriter->writeElement(
				'packagename',
				$this->packageXmlData['packageName_i18n'][$defaultLanguageID],
				[],
				$this->requiresCdata($this->packageXmlData['packageName_i18n'][$defaultLanguageID])
			);
			
			foreach ($this->packageXmlData['packageName_i18n'] as $languageID => $packageName) {
				if ($languageID !== $defaultLanguageID) {
					$this->xmlWriter->writeElement(
						'packagename',
						$packageName,
						['language' => LanguageFactory::getInstance()->getLanguage($languageID)->languageCode],
						$this->requiresCdata($packageName)
					);
				}
			}
		}
		else {
			$this->xmlWriter->writeElement(
				'packagename',
				$this->packageXmlData['packageName'],
				[],
				$this->requiresCdata($this->packageXmlData['packageName'])
			);
		}
		
		if (isset($this->packageXmlData['packageDescription_i18n'])) {
			$defaultLanguageID = null;
			if (isset($this->packageXmlData['packageDescription_i18n'][$english->languageID])) {
				$defaultLanguageID = $english->languageID;
			}
			else {
				reset($this->packageXmlData['packageDescription_i18n']);
				$defaultLanguageID = key($this->packageXmlData['packageDescription_i18n']);
			}
			
			$this->xmlWriter->writeElement(
				'packagedescription',
				$this->packageXmlData['packageDescription_i18n'][$defaultLanguageID],
				[],
				$this->requiresCdata($this->packageXmlData['packageDescription_i18n'][$defaultLanguageID])
			);
			
			foreach ($this->packageXmlData['packageDescription_i18n'] as $languageID => $packageDescription) {
				if ($languageID !== $defaultLanguageID) {
					$this->xmlWriter->writeElement(
						'packagedescription',
						$packageDescription,
						['language' => LanguageFactory::getInstance()->getLanguage($languageID)->languageCode],
						$this->requiresCdata($packageDescription)
					);
				}
			}
		}
		else {
			$this->xmlWriter->writeElement(
				'packagedescription',
				$this->packageXmlData['packageDescription'],
				[],
				$this->requiresCdata($this->packageXmlData['packageDescription'])
			);
		}
		
		if (!empty($this->packageXmlData['isApplication'])) {
			$this->xmlWriter->writeElement(
				'isapplication',
				intval($this->packageXmlData['isApplication']),
				[],
				false
			);
		}
		if (!empty($this->packageXmlData['applicationDirectory'])) {
			$this->xmlWriter->writeElement(
				'applicationdirectory',
				$this->packageXmlData['applicationDirectory'],
				[],
				false
			);
		}
		$this->xmlWriter->writeElement(
			'version',
			$this->packageXmlData['version'],
			[],
			false
		);
		$this->xmlWriter->writeElement(
			'date',
			$this->packageXmlData['date'],
			[],
			false
		);
		if (!empty($this->packageXmlData['packageUrl'])) {
			$this->xmlWriter->writeElement(
				'packageurl',
				$this->packageXmlData['packageUrl'],
				[],
				$this->requiresCdata($this->packageXmlData['packageUrl'])
			);
		}
		
		$this->xmlWriter->endElement();
	}
	
	/**
	 * Writes the `optionalpackages` element.
	 */
	protected function writeRequiredPackages() {
		if (!empty($this->packageXmlData['requiredPackages'])) {
			$this->xmlWriter->startElement('requiredpackages');
			
			foreach ($this->packageXmlData['requiredPackages'] as $requiredPackage) {
				$attributes = [];
				if (!empty($requiredPackage['minVersion'])) {
					$attributes['minversion'] = $requiredPackage['minVersion'];
				}
				if (!empty($requiredPackage['file'])) {
					$attributes['file'] = "requirements/{$requiredPackage['packageIdentifier']}.tar";
				}
				
				$this->xmlWriter->writeElement(
					'requiredpackage',
					$requiredPackage['packageIdentifier'],
					$attributes,
					false
				);
			}
			
			$this->xmlWriter->endElement();
		}
	}
}
