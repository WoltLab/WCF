<?php
namespace wcf\system\form\builder\field;
use wcf\system\WCF;

/**
 * Provides default implementations of `ISuffixedFormField` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
trait TSuffixedFormField {
	/**
	 * suffix of this field
	 * @var	null|string
	 */
	protected $suffix;
	
	/**
	 * Returns the suffix of this field or `null` if no suffix has been set.
	 * 
	 * @return	null|string
	 */
	public function getSuffix() {
		return $this->suffix;
	}
	
	/**
	 * Sets the suffix of this field using the given language item and returns
	 * this element. If `null` is passed, the suffix is removed.
	 * 
	 * @param	null|string	$languageItem	language item containing the suffix or `null` to unset suffix
	 * @param	array		$variables	additional variables used when resolving the language item
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given language item is no or otherwise invalid
	 */
	public function suffix($languageItem = null, array $variables = []) {
		if ($languageItem === null) {
			if (!empty($variables)) {
				throw new \InvalidArgumentException("Cannot use variables when unsetting suffix of field '{$this->getId()}'");
			}
			
			$this->suffix = null;
		}
		else {
			if (!is_string($languageItem)) {
				throw new \InvalidArgumentException("Given suffix language item is no string, " . gettype($languageItem) . " given.");
			}
			
			$this->suffix = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);
		}
		
		return $this;
	}
}
