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
 * @package	WoltLabSuite\Core\System\Option
 */
class FileSizeOptionType extends TextOptionType {
	/**
	 * @inheritDoc
	 */
	protected $inputClass = 'short textRight';
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		$number = str_replace(WCF::getLanguage()->get('wcf.global.thousandsSeparator'), '', $newValue);
		$number = str_replace(WCF::getLanguage()->get('wcf.global.decimalPoint'), '.', $number);
		
		if (!preg_match('~^(?:\d*)\.?\d+~', $number, $matches)) return 0;
		
		$number = $matches[0];
		if (preg_match('/[kmgt]i?b$/i', $newValue, $multiplier)) {
			switch (mb_strtolower($multiplier[0])) {
				/** @noinspection PhpMissingBreakStatementInspection */
				case 'tb':
					$number *= 1000;
				/** @noinspection PhpMissingBreakStatementInspection */
				case 'gb':
					$number *= 1000;
				/** @noinspection PhpMissingBreakStatementInspection */
				case 'mb':
					$number *= 1000;
				case 'kb':
					$number *= 1000;
				break;
				/** @noinspection PhpMissingBreakStatementInspection */
				case 'tib':
					$number *= 1024;
				/** @noinspection PhpMissingBreakStatementInspection */
				case 'gib':
					$number *= 1024;
				/** @noinspection PhpMissingBreakStatementInspection */
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
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		$value = FileUtil::formatFilesize($value);
		return parent::getFormElement($option, $value);
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
