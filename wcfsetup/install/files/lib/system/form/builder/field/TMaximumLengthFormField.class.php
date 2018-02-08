<?php
namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IMaximumLengthFormField` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
trait TMaximumLengthFormField {
	/**
	 * maximum length of the field value
	 * @var	null|int
	 */
	protected $__maximumLength;
	
	/**
	 * Returns the maximum length of the values of this field or `null` if no placeholder
	 * has been set.
	 * 
	 * @return	null|int
	 */
	public function getMaximumLength() {
		return $this->__maximumLength;
	}
	
	/**
	 * Sets the maximum length of the values of this field. If `null` is passed, the
	 * maximum length is removed.
	 * 
	 * @param	null|int	$maximumLength	maximum field value length
	 * @return	IMaximumLengthFormField		this field
	 * 
	 * @throws	\InvalidArgumentException	if the given maximum length is no integer or otherwise invalid
	 */
	public function maximumLength($maximumLength = null): IMaximumLengthFormField {
		if ($maximumLength !== null) {
			if (!is_int($maximumLength)) {
				throw new \InvalidArgumentException("Given maximum length is no int, '" . gettype($maximumLength) . "' given.");
			}
			
			if ($maximumLength <= 0) {
				throw new \InvalidArgumentException("Maximum length must be positive, '{$maximumLength}' given.");
			}
			
			if ($this instanceof IMinimumLengthFormField) {
				$minimumLength = $this->getMinimumLength();
				if ($minimumLength !== null && $minimumLength > $maximumLength) {
					throw new \InvalidArgumentException("Minimum length ({$minimumLength}) cannot be greater than maximum length ({$maximumLength}).");
				}
			}
		}
		
		$this->__maximumLength = $maximumLength;
		
		return $this;
	}
}
