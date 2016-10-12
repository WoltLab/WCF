<?php
namespace wcf\system\option;
use wcf\data\option\Option;

/**
 * Option type implementation for url input fields.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class URLOptionType extends TextOptionType {
	/**
	 * @inheritDoc
	 */
	protected function getContent(Option $option, $newValue) {
		if ($newValue && !preg_match('~^https?://~i', $newValue)) {
			$newValue = 'http://'.$newValue;
		}
		
		return parent::getContent($option, $newValue);
	}
}
