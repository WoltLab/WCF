<?php
namespace wcf\system\form\builder\field;

/**
 * Represents a form field that supports i18n input.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
interface II18nFormField extends IFormField {
	/**
	 * Returns the pattern for the language item used to save the i18n values.
	 * 
	 * @return	string				language item pattern
	 * 
	 * @throws	\BadMethodCallException		if i18n is disabled for this field or no language item has been set
	 */
	public function getLanguageItemPattern(): string;
	
	/**
	 * Returns `true` if the current field value is a i18n value and returns `false`
	 * otherwise or if no value has been set.
	 * 
	 * @return	bool
	 */
	public function hasI18nValues(): bool;
	
	/**
	 * Returns `true` if the current field value is a plain value and returns `false`
	 * otherwise or if no value has been set.
	 *
	 * @return	bool
	 */
	public function hasPlainValue(): bool;
	
	/**
	 * Sets whether this field is supports i18n input and returns this field.
	 * 
	 * @param	bool		$i18n		determines if field is supports i18n input
	 * @return	static				this field
	 */
	public function i18n(bool $i18n = true): II18nFormField;
	
	/**
	 * Sets whether this field's value must be i18n input and returns this field.
	 * 
	 * If this method sets that the field's value must be i18n input, it also must
	 * ensure that i18n support is enabled.
	 * 
	 * @param	bool		$i18nRequired		determines if field value must be i18n input
	 * @return	static					this field
	 */
	public function i18nRequired(bool $i18nRequired = true): II18nFormField;
	
	/**
	 * Returns `true` if this field supports i18n input and returns `false` otherwise.
	 * By default, fields do not support i18n input.
	 * 
	 * @return	bool
	 */
	public function isI18n(): bool;
	
	/**
	 * Returns `true` if this field's value must be i18n input and returns `false` otherwise.
	 * By default, fields do not support i18n input.
	 * 
	 * @return	bool
	 */
	public function isI18nRequired(): bool;
	
	/**
	 * Sets the pattern for the language item used to save the i18n values
	 * and returns this field.
	 * 
	 * @param	string		$pattern	language item pattern
	 * @return	static				this field
	 * 
	 * @throws	\BadMethodCallException		if i18n is disabled for this field
	 * @throws	\InvalidArgumentException	if the given pattern is invalid
	 */
	public function languageItemPattern(string $pattern): II18nFormField;
}
