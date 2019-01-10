<?php
namespace wcf\system\form\builder\field\data\processor;
use wcf\system\form\builder\IFormDocument;

/**
 * Field data processor implementation that voids a certain data property.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Data\Processor
 * @since	5.2
 */
class VoidFormFieldDataProcessor implements IFormFieldDataProcessor {
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
	public function __invoke(IFormDocument $document, array $parameters) {
		if ($this->isDataProperty) {
			if (isset($parameters['data'][$this->property])) {
				unset($parameters['data'][$this->property]);
			}
		}
		else if (isset($parameters[$this->property])) {
			unset($parameters[$this->property]);
		}
		
		return $parameters;
	}
}
