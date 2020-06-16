<?php
namespace wcf\system\form\builder;
use wcf\system\WCF;

/**
 * Provides default implementations of `IFormElement` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	5.2
 */
trait TFormElement {
	use TFormNode;
	
	/**
	 * description of this element
	 * @var	string
	 */
	protected $description;
	
	/**
	 * label of this element
	 * @var	string
	 */
	protected $label;
	
	/**
	 * Sets the description of this element using the given language item
	 * and returns this element. If `null` is passed, the element description
	 * is removed.
	 * 
	 * @param	null|string	$languageItem	language item containing the element description or `null` to unset description
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
			
			$this->description = null;
		}
		else {
			if (!is_string($languageItem)) {
				throw new \InvalidArgumentException("Given description language item is no string, " . gettype($languageItem) . " given.");
			}
			
			$this->description = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);
		}
		
		return $this;
	}
	
	/**
	 * Returns the description of this element or `null` if no description has been set.
	 * 
	 * @return	null|string	element description
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Returns the label of this element or `null` if no label has been set.
	 * 
	 * @return	null|string	element label
	 */
	public function getLabel() {
		return $this->label;
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
			
			$this->label = null;
		}
		else {
			if (!is_string($languageItem)) {
				throw new \InvalidArgumentException("Given label language item is no string, " . gettype($languageItem) . " given.");
			}
			
			$this->label = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);
		}
		
		return $this;
	}
	
	/**
	 * Returns `true` if this element requires a label to be set.
	 * 
	 * @return	bool
	 */
	public function requiresLabel() {
		// by default, form elements do not require a label 
		return false;
	}
}
