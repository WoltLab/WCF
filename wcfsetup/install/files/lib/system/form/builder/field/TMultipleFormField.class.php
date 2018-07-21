<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\field\data\CustomFormFieldDataProcessor;
use wcf\system\form\builder\IFormDocument;

/**
 * Provides default implementations of `IMultipleFormField` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
trait TMultipleFormField {
	/**
	 * maximum number of values that can be selected or set
	 * @var	int
	 */
	protected $__maximumMultiples = IMultipleFormField::NO_MAXIMUM_MULTIPLES;
	
	/**
	 * minimum number of values that can be selected or set
	 * @var	int
	 */
	protected $__minimumMultiples = 0;
	
	/**
	 * `true` if this field allows multiple values to be selected or set and `false` otherwise
	 * @var	bool
	 */
	protected $__multiple = false;
	
	/**
	 * Returns `true` if multiple values can be selected or set and returns `false`
	 * otherwise.
	 *
	 * Per default, fields do not allow multiple values.
	 *
	 * @return	bool
	 */
	public function allowsMultiple() {
		return $this->__multiple;
	}
	
	/**
	 * Returns the maximum number of values that can be selected or set.
	 * If there is no maximum number, `IMultipleFormField::NO_MAXIMUM_MULTIPLES`
	 * is returned.
	 *
	 * @return	int	maximum number of values
	 */
	public function getMaximumMultiples() {
		return $this->__maximumMultiples;
	}
	
	/**
	 * Returns the minimum number of values that can be selected or set.
	 *
	 * Per default, there is no minimum number.
	 *
	 * @return	int	minimum number of values
	 */
	public function getMinimumMultiples() {
		return $this->__minimumMultiples;
	}
	
	/**
	 * Returns `true` if this field provides a value that can simply be stored
	 * in a column of the database object's database table and returns `false`
	 * otherwise.
	 * 
	 * Note: If `false` is returned, this field should probabily add its own
	 * `IFormFieldDataProcessor` object to the form document's data processor.
	 * A suitable place to add the processor is the `populate()` method.
	 * 
	 * @return	bool
	 */
	public function hasSaveValue() {
		return !$this->allowsMultiple();
	}
	
	/**
	 * Sets the maximum number of values that can be selected or set and returns
	 * this field. To unset the maximum number, pass `IMultipleFormField::NO_MAXIMUM_MULTIPLES`.
	 *
	 * @param	int		$maximum	maximum number of values
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given maximum number of values is invalid
	 */
	public function maximumMultiples(int $maximum) {
		if ($maximum !== IMultipleFormField::NO_MAXIMUM_MULTIPLES) {
			if ($maximum <= 0) {
				throw new \InvalidArgumentException("The maximum number of values has to be positive, '{$maximum}' given.");
			}
			
			if ($this->getMinimumMultiples() !== 0 && $maximum < $this->getMinimumMultiples()) {
				throw new \InvalidArgumentException("The given maximum number of values '{$maximum}' is less than the set minimum number of values '{$this->getMinimumMultiples()}'.");
			}
		}
		
		$this->__maximumMultiples = $maximum;
		
		return $this;
	}
	
	/**
	 * Sets the minimum number of values that can be selected or set and returns
	 * this field.
	 * 
	 * @param	int		$maximum	maximum number of values
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given minimum number of values is invalid
	 */
	public function minimumMultiples(int $minimum) {
		if ($minimum < 0) {
			throw new \InvalidArgumentException("The minimum number of values has to be non-negative, '{$minimum}' given.");
		}
		
		if ($this->getMaximumMultiples() !== IMultipleFormField::NO_MAXIMUM_MULTIPLES && $minimum > $this->getMaximumMultiples()) {
			throw new \InvalidArgumentException("The given minimum number of values '{$minimum}' is greater than the set maximum number of values '{$this->getMaximumMultiples()}'.");
		}
		
		$this->__minimumMultiples = $minimum;
		
		return $this;
	}
	
	/**
	 * Sets whether multiple values can be selected or set and returns this field.
	 *
	 * @param	bool		$multiple	determines if multiple values can be selected/set
	 * @return	static		this field
	 */
	public function multiple(bool $multiple = true) {
		$this->__multiple = $multiple;
		
		return $this;
	}
	
	/**
	 * Is called once after all nodes have been added to the document this node belongs to.
	 * 
	 * This method enables this node to perform actions that require the whole document having
	 * finished constructing itself and every parent-child relationship being established.
	 * 
	 * @return	static				this node
	 * 
	 * @throws	\BadMethodCallException		if this node has already been populated
	 */
	public function populate() {
		parent::populate();
		
		if ($this->allowsMultiple()) {
			$this->getDocument()->getDataHandler()->add(new CustomFormFieldDataProcessor('multiple', function(IFormDocument $document, array $parameters) {
				if ($this->checkDependencies() && !empty($this->getValue())) {
					$parameters[$this->getObjectProperty()] = $this->getValue();
				}
				
				return $parameters;
			}));
		}
		
		return $this;
	}
	
	/**
	 * Sets the value of this field and returns this field.
	 * 
	 * @param	mixed		$value		new field value
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given value is of an invalid type or otherwise is invalid
	 */
	public function value($value) {
		// ensure array value for form fields that actually support multiple values;
		// allows enabling support for multiple values for existing fields
		if ($this->allowsMultiple() && !is_array($value)) {
			$value = [$value];
		} 
		
		return parent::value($value);
	}
}
