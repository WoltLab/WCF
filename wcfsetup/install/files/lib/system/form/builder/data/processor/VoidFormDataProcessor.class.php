<?php
namespace wcf\system\form\builder\data\processor;
use wcf\system\form\builder\IFormDocument;

/**
 * Field data processor implementation that voids a certain data property.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Data\Processor
 * @since	5.2
 */
class VoidFormDataProcessor extends AbstractFormDataProcessor {
	/**
	 * is `true` if the property is stored in the `data` array
	 * @var	bool
	 */
	protected $isDataProperty;
	
	/**
	 * name of the voided property
	 * @var	string
	 */
	protected $property;
	
	/**
	 * Initializes a new CustomFormFieldDataProcessor object.
	 * 
	 * @param	string	$property		name of the voided property
	 * @param	bool	$isDataProperty		is `true` if the property is stored in the `data` array
	 */
	public function __construct($property, $isDataProperty = true) {
		$this->property = $property;
		$this->isDataProperty = $isDataProperty;
	}
	
	/**
	 * @inheritDoc
	 */
	public function processFormData(IFormDocument $document, array $parameters) {
		if ($this->isDataProperty) {
			if (array_key_exists($this->property, $parameters['data'])) {
				unset($parameters['data'][$this->property]);
			}
		}
		else if (array_key_exists($this->property, $parameters)) {
			unset($parameters[$this->property]);
		}
		
		return $parameters;
	}
}
