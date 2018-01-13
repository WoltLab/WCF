<?php
namespace wcf\system\form\builder\field\dependency;

/**
 * Represents a dependency that requires that requires a field to have a certain value.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Dependency
 * @since	3.2
 */
class ValueFormFieldDependency extends AbstractFormFieldDependency {
	/**
	 * possible values the field may have for the dependency to be met
	 * @var	null|array
	 */
	protected $__values;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__valueFormFieldDependency';
	
	/**
	 * @inheritDoc
	 */
	public function checkDependency() {
		return in_array($this->getField()->getValue(), $this->getValues());
	}
	
	/**
	 * Returns the possible values the field may have for the dependency to be met.
	 * 
	 * @return	array				possible field values
	 * 
	 * @throws	\BadMethodCallException		if no values have been set
	 */
	public function getValues() {
		if ($this->__values === null) {
			throw new \BadMethodCallException("Values have not been set for dependency '{$this->getId()}' on node '{$this->getDependentNode()->getId()}'.");
		}
		
		return $this->__values;
	}
	
	/**
	 * Sets the possible values the field may have for the dependency to be met.
	 * 
	 * @param	array		$values		possible field values
	 * @return	static		$this		this dependency
	 * 
	 * @throws	\InvalidArgumentException	if given values are invalid
	 */
	public function values(array $values) {
		if (empty($values)) {
			throw new \InvalidArgumentException("Given values are empty.");
		}
		foreach ($values as $value) {
			if (!is_string($value) && !is_numeric($value)) {
				throw new \InvalidArgumentException("Values contains invalid value of type '" . gettype($value) . "', only strings or numbers are allowed.");
			}
		}
		
		$this->__values = $values;
		
		return $this;
	}
}
