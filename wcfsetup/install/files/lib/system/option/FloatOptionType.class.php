<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\WCF;

/**
 * Option type implementation for float values.
 * 
 * @author	Tobias Friebel
 * @copyright	2001-2011 Tobias Friebel
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class FloatOptionType extends TextOptionType {
	/**
	 * @see	\wcf\system\option\TextOptionType::$inputClass
	 */
	protected $inputClass = 'medium';
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		$value = str_replace('.', WCF::getLanguage()->get('wcf.global.decimalPoint'), $value);
		
		return parent::getFormElement($option, $value);
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		$newValue = str_replace(' ', '', $newValue);
		$newValue = str_replace(WCF::getLanguage()->get('wcf.global.thousandsSeparator'), '', $newValue);
		$newValue = str_replace(WCF::getLanguage()->get('wcf.global.decimalPoint'), '.', $newValue);
		return floatval($newValue);
	}
}
