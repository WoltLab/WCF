<?php
namespace wcf\system\form\builder\field;
use wcf\data\DatabaseObjectList;
use wcf\data\ITitledObject;
use wcf\system\WCF;
use wcf\util\ClassUtil;

/**
 * Provides default implementations of `ISelectionFormField` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
trait TSelectionFormField {
	/**
	 * `true` if this field's options are filterable by the user
	 * @var	bool
	 */
	protected $__filterable = false;
	
	/**
	 * structured options array used to generate the form field output
	 * @var	null|array
	 */
	protected $__nestedOptions;
	
	/**
	 * possible options to select
	 * @var	null|array
	 */
	protected $__options;
	
	/**
	 * Sets if the selection options can be filtered by the user so that they
	 * are able to quickly find the desired option out of a larger list of
	 * available options.
	 * 
	 * @param	bool	$filterable	determines if field's options are filterable by user
	 * @return	static			this node
	 */
	public function filterable($filterable = true) {
		$this->__filterable = $filterable;
		
		return $this;
	}
	
	/**
	 * Returns a structured array that can be used to generate the form field output.
	 * 
	 * Array elements are `value`, `label`, and `depth`.
	 * 
	 * @return	array
	 * @throws	\BadMethodCallException		if nested options are not supported
	 */
	public function getNestedOptions() {
		if (!$this->supportsNestedOptions()) {
			throw new \BadMethodCallException("Nested options are not supported.");
		}
		
		return $this->__nestedOptions;
	}
	
	/**
	 * Returns the possible options of this field.
	 * 
	 * @return	array
	 * 
	 * @throws	\BadMethodCallException		if no options have been set
	 */
	public function getOptions() {
		return $this->__options;
	}
	
	/**
	 * Returns `true` if this node is available and returns `false` otherwise.
	 * 
	 * If the node's availability has not been explicitly set, `true` is returned.
	 * 
	 * @return	bool
	 * 
	 * @see		IFormNode::available()
	 */
	public function isAvailable() {
		// selections without any possible values are not available
		return !empty($this->__options) && parent::isAvailable();
	}
	
	/**
	 * Returns `true` if the selection options can be filtered by the user so
	 * that they are able to quickly find the desired option out of a larger
	 * list of available options and returns `false` otherwise.
	 * 
	 * Per default, fields are not filterable.
	 * 
	 * @return	bool
	 */
	public function isFilterable() {
		return $this->__filterable;
	}
	
	/**
	 * Sets the possible options of this field and returns this field.
	 * 
	 * Note: If PHP considers the key of the first selectable option to be empty
	 * and the this field is nullable, then the save value of that key is `null`
	 * instead of the given empty value.
	 * 
	 * If a `callable` is passed, it is expected that it either returns an array
	 * or a `DatabaseObjectList` object.
	 * 
	 * If a `DatabaseObjectList` object is passed and `$options->objectIDs === null`,
	 * `$options->readObjects()` is called so that the `readObjects()` does not have
	 * to be called by the API user.
	 * 
	 * If nested options are passed, the given options must be a array or a
	 * callable returning an array. Each array value must be an array with the
	 * following entries: `depth`, `label`, and `value`.
	 * 
	 * @param	array|callable|DatabaseObjectList	$options	selectable options or callable returning the options
	 * @param	bool					$nestedOptions	is `true` if the passed options are nested options
	 * @param	bool					$labelLanguageItems	is `true` if the labels should be treated as language items if possible
	 * @return	static					this field
	 * 
	 * @throws	\InvalidArgumentException	if given options are no array or callable or otherwise invalid
	 * @throws	\UnexpectedValueException	if callable does not return an array
	 */
	public function options($options, $nestedOptions = false, $labelLanguageItems = true) {
		if ($nestedOptions) {
			if (!is_array($options) && !is_callable($options)) {
				throw new \InvalidArgumentException("The given nested options are neither an array nor a callable, " . gettype($options) . " given.");
			}
		}
		else if (!is_array($options) && !is_callable($options) && !($options instanceof DatabaseObjectList)) {
			throw new \InvalidArgumentException("The given options are neither an array, a callable nor an instance of '" . DatabaseObjectList::class . "', " . gettype($options) . " given.");
		}
		
		if (is_callable($options)) {
			$options = $options();
			
			if ($nestedOptions) {
				if (!is_array($options) && !($options instanceof DatabaseObjectList)) {
					throw new \UnexpectedValueException("The nested options callable is expected to return an array, " . gettype($options) . " returned.");
				}
			}
			else if (!is_array($options) && !($options instanceof DatabaseObjectList)) {
				throw new \UnexpectedValueException("The options callable is expected to return an array or an instance of '" . DatabaseObjectList::class . "', " . gettype($options) . " returned.");
			}
			
			return $this->options($options, $nestedOptions, $labelLanguageItems);
		}
		else if ($options instanceof DatabaseObjectList) {
			// automatically read objects
			if ($options->objectIDs === null) {
				$options->readObjects();
			}
			
			$dboOptions = [];
			foreach ($options as $object) {
				if (!ClassUtil::isDecoratedInstanceOf($object, ITitledObject::class)) {
					throw new \InvalidArgumentException("The database objects in the passed list must implement '" . ITitledObject::class . "'.");
				}
				if (!$object::getDatabaseTableIndexIsIdentity()) {
					throw new \InvalidArgumentException("The database objects in the passed list must must have an index that identifies the objects.");
				}
				
				$dboOptions[$object->getObjectID()] = $object->getTitle();
			}
			
			$options = $dboOptions;
		}
		
		$this->__options = [];
		if ($nestedOptions) {
			foreach ($options as $key => &$option) {
				if (!is_array($option)) {
					throw new \InvalidArgumentException("Nested option with key '{$key}' has is no array.");
				}
				if (count($option) !== 3) {
					throw new \InvalidArgumentException("Nested option with key '{$key}' does not contain three elements.");
				}
				
				// check if all required elements exist
				foreach (['label', 'value', 'depth'] as $entry) {
					if (!isset($option[$entry])) {
						throw new \InvalidArgumentException("Nested option with key '{$key}' has no {$entry} entry.");
					}
				}
				
				// validate label
				if (is_object($option['label']) && method_exists($option['label'], '__toString')) {
					$option['label'] = (string) $option['label'];
				}
				else if (!is_string($option['label']) && !is_numeric($option['label'])) {
					throw new \InvalidArgumentException("Nested option with key '{$key}' contain invalid label of type " . gettype($option['label']) . ".");
				}
				
				// resolve language item for label
				if ($labelLanguageItems && preg_match('~^([a-zA-Z0-9-_]+\.){2,}[a-zA-Z0-9-_]+$~', (string) $option['label'])) {
					$option['label'] = WCF::getLanguage()->getDynamicVariable($option['label']);
				}
				
				// validate value
				if (!is_string($option['value']) && !is_numeric($option['value'])) {
					throw new \InvalidArgumentException("Nested option with key '{$key}' contain invalid value of type " . gettype($option['label']) . ".");
				}
				else if (isset($this->__options[$option['value']])) {
					throw new \InvalidArgumentException("Options values must be unique, but '{$option['value']}' appears at least twice as value.");
				}
				
				// save value
				$this->__options[$option['value']] = $option['label'];
				
				// validate depth
				if (!is_int($option['depth'])) {
					throw new \InvalidArgumentException("Depth of nested option with key '{$key}' is no integer, " . gettype($options) . " given.");
				}
				if ($option['depth'] < 0) {
					throw new \InvalidArgumentException("Depth of nested option with key '{$key}' is negative.");
				}
			}
			unset($option);
			
			$this->__nestedOptions = $options;
		}
		else {
			foreach ($options as $value => $label) {
				if (is_array($label)) {
					throw new \InvalidArgumentException("Non-nested options must not contain any array. Array given for value '{$value}'.");
				}
				
				if (is_object($label) && method_exists($label, '__toString')) {
					$label = (string) $label;
				}
				else if (!is_string($label) && !is_numeric($label)) {
					throw new \InvalidArgumentException("Options contain invalid label of type " . gettype($label) . ".");
				}
				
				if (isset($this->__options[$value])) {
					throw new \InvalidArgumentException("Options values must be unique, but '{$value}' appears at least twice as value.");
				}
				
				// resolve language item for label
				if ($labelLanguageItems && preg_match('~^([a-zA-Z0-9-_]+\.){2,}[a-zA-Z0-9-_]+$~', (string) $label)) {
					$label = WCF::getLanguage()->getDynamicVariable($label);
				}
				
				$this->__options[$value] = $label;
			}
			
			// ensure that `$this->__nestedOptions` is always populated
			// for form field that support nested options
			if ($this->supportsNestedOptions()) {
				$this->__nestedOptions = [];
				
				foreach ($this->__options as $value => $label) {
					$this->__nestedOptions[] = [
						'depth' => 0,
						'label' => $label,
						'value' => $value
					];
				}
			}
		}
		
		if ($this->__nestedOptions === null) {
			$this->__nestedOptions = [];
		}
		
		return $this;
	}
	
	/**
	 * Returns `true` if the field class supports nested options and `false` otherwise.
	 *
	 * @return	bool
	 */
	public function supportsNestedOptions() {
		return true;
	}
}
