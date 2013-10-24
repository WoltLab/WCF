<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Option type implementation for file sizes.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class FileSizeOptionType extends TextOptionType {
	/**
	 * @see	\wcf\system\option\TextOptionType::$inputClass
	 */
	protected $inputClass = 'medium';
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		$number = str_replace(WCF::getLanguage()->get('wcf.global.thousandsSeparator'), '', $newValue);
		$number = str_replace(WCF::getLanguage()->get('wcf.global.decimalPoint'), '.', $number);
		
		if (!preg_match('~^(?:\d*)\.?\d+~', $number, $matches)) return 0;
		
		$number = $matches[0];
		if (preg_match('/[kmgt]i?b$/i', $newValue, $multiplier)) {
			switch (mb_strtolower($multiplier[0])) {
				case 'tb':
					$number *= 1000;
				case 'gb':
					$number *= 1000;
				case 'mb':
					$number *= 1000;
				case 'kb':
					$number *= 1000;
				break;
				case 'tib':
					$number *= 1024;
				case 'gib':
					$number *= 1024;
				case 'mib':
					$number *= 1024;
				case 'kib':
					$number *= 1024;
				break;
			}
		}
		
		return $number;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		$value = FileUtil::formatFileSize($value);
		return parent::getFormElement($option, $value);
	}
}
