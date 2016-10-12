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
 * @package	WoltLabSuite\Core\System\Option
 */
class FloatOptionType extends TextOptionType {
	/**
	 * @inheritDoc
	 */
	protected $inputClass = 'short textRight';
	
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		$value = str_replace('.', WCF::getLanguage()->get('wcf.global.decimalPoint'), $value);
		
		return parent::getFormElement($option, $value);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		$newValue = str_replace(' ', '', $newValue);
		$newValue = str_replace(WCF::getLanguage()->get('wcf.global.thousandsSeparator'), '', $newValue);
		$newValue = str_replace(WCF::getLanguage()->get('wcf.global.decimalPoint'), '.', $newValue);
		return floatval($newValue);
	}
	
	/**
	 * @inheritDoc
	 */
	public function compare($value1, $value2) {
		if ($value1 == $value2) {
			return 0;
		}
		
		return ($value1 > $value2) ? 1 : -1;
	}
}
