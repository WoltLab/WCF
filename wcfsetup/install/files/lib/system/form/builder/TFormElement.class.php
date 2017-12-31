<?php
namespace wcf\system\form\builder;
use wcf\system\form\builder\field\dependency\IFormFieldDependency;
use wcf\system\WCF;

/**
 * Provides default implementations of `IFormElement` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	3.2
 */
trait TFormElement {
	use TFormNode;
	
	/**
	 * description of this element
	 * @var	string
	 */
	protected $__description;
	
	/**
	 * label of this element
	 * @var	string
	 */
	protected $__label;
	
	/**
	 * dependencies of this element
	 * @var	IFormFieldDependency[]
	 */
	protected $dependencies = [];
	
	/**
	 * Adds a dependency on the value of a `IFormField` so that this element is
	 * only available if the field satisfies the given dependency and returns
	 * this element.
	 *
	 * This method is expected to set the dependent element of the given dependency
	 * to this element.
	 *
	 * @param	IFormFieldDependency		$dependency	added element dependency
	 * @return	static						this element
	 */
	public function addDependency(IFormFieldDependency $dependency) {
		$this->dependencies[] = $dependency;
		
		$dependency->dependentElement($this);
		
		return $this;
	}
	
	/**
	 * Sets the description of this element using the given language item
	 * and returns this element. If `null` is passed, the element description
	 * is removed.
	 *
	 * @param	null|string	$languageItem	language item containing the element description or `null` to unsed description
	 * @param	array		$variables	additional variables used when resolving the language item
	 * @return	static				this element
	 *
	 * @throws	\InvalidArgumentException	if the given description is no string or otherwise is invalid
	 */
	public function description($languageItem = null, array $variables = []) {
		if ($languageItem === null) {
			if (!empty($variables)) {
				throw new \InvalidArgumentException("Cannot use variables when unsetting description of element '{$this->getId()}'");
			}
			
			$this->__description = null;
		}
		else {
			if (!is_string($languageItem)) {
				throw new \InvalidArgumentException("Given description language item is no string, " . gettype($languageItem) . " given.");
			}
			
			$this->__description = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);
		}
		
		return $this;
	}
	
	/**
	 * Returns the description of this element or `null` if no description has been set.
	 *
	 * @return	null|string	element description
	 */
	public function getDescription() {
		return $this->__description;
	}
	
	/**
	 * Returns the label of this element or `null` if no label has been set.
	 *
	 * @return	null|string	element label
	 */
	public function getLabel() {
		return $this->__label;
	}
	
	/**
	 * Returns `true` if this element has a dependency with the given id and
	 * returns `false` otherwise.
	 * 
	 * @param	string		$dependencyId	id of the checked dependency
	 * @return	bool
	 * 
	 * @throws	\InvalidArgumentException	if the given id is no string or otherwise invalid
	 */
	public function hasDependency($dependencyId) {
		foreach ($this->dependencies as $dependency) {
			if ($dependency->getId() === $dependencyId) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Sets the label of this element using the given language item and
	 * returns this element. If `null` is passed, the element label is
	 * removed.
	 *
	 * @param	null|string	$languageItem	language item containing the element label or `null` to unset label
	 * @param	array		$variables	additional variables used when resolving the language item
	 * @return	static				this element
	 *
	 * @throws	\InvalidArgumentException	if the given label is no string or otherwise is invalid
	 */
	public function label($languageItem = null, array $variables = []) {
		if ($languageItem === null) {
			if (!empty($variables)) {
				throw new \InvalidArgumentException("Cannot use variables when unsetting label of element '{$this->getId()}'");
			}
			
			$this->__label = null;
		}
		else {
			if (!is_string($languageItem)) {
				throw new \InvalidArgumentException("Given description language item is no string, " . gettype($languageItem) . " given.");
			}
			
			$this->__label = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);
		}
		
		return $this;
	}
	
	/**
	 * Removes the dependency with the given id and returns this element.
	 * 
	 * @param	string		$dependencyId	id of the removed dependency
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given id is no string or otherwise invalid or no such dependency exists
	 */
	public function removeDependency($dependencyId) {
		foreach ($this->dependencies as $key => $dependency) {
			if ($dependency->getId() === $dependencyId) {
				unset($this->dependencies[$key]);
				
				return $this;
			}
		}
		
		throw new \InvalidArgumentException("Unknown dependency with id '{$dependencyId}'.");
	}
}
