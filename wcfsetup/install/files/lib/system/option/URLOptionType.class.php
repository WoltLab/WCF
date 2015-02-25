<?php
namespace wcf\system\option;
use wcf\data\option\Option;

/**
 * Option type implementation for url input fields.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class URLOptionType extends TextOptionType {
	/**
	 * @see	\wcf\system\option\TextOptionType::getContent()
	 */
	protected function getContent(Option $option, $newValue) {
		if ($newValue && !preg_match('~^https?://~i', $newValue)) {
			$newValue = 'http://'.$newValue;
		}
		
		return parent::getContent($option, $newValue);
	}
}
