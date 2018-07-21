<?php
namespace wcf\system\form\builder;

/**
 * Represents an element of a form that can have a description, a label and dependencies.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	3.2
 */
interface IFormElement extends IFormNode {
	/**
	 * Sets the description of this element using the given language item
	 * and returns this element. If `null` is passed, the element description
	 * is removed.
	 * 
	 * @param	null|string	$languageItem	language item containing the element description or `null` to unset description
	 * @param	array		$variables	additional variables used when resolving the language item
	 * @return	static				this element
	 * 
	 * @throws	\InvalidArgumentException	if the given description is invalid
	 */
	public function description(string $languageItem = null, array $variables = []);
	
	/**
	 * Returns the description of this element or `null` if no description has been set.
	 * 
	 * @return	null|string	element description
	 */
	public function getDescription();
	
	/**
	 * Returns the label of this element or `null` if no label has been set.
	 * 
	 * @return	null|string	element label
	 */
	public function getLabel();
	
	/**
	 * Sets the label of this element using the given language item and
	 * returns this element. If `null` is passed, the element label is
	 * removed.
	 *
	 * @param	null|string	$languageItem	language item containing the element label or `null` to unset label
	 * @param	array		$variables	additional variables used when resolving the language item
	 * @return	static				this element
	 * 
	 * @throws	\InvalidArgumentException	if the given label is invalid
	 */
	public function label(string $languageItem = null, array $variables = []);
	
	/**
	 * Returns `true` if this element requires a label to be set.
	 * 
	 * @return	bool
	 */
	public function requiresLabel();
}
