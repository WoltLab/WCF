<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\WCF;

/**
 * Option type implementation for Prism highlighters selection.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class HighlightersOptionType extends MultiSelectOptionType {
	/**
	 * @inheritDoc
	 */
	protected function getSelectOptions(Option $option) {
		$meta = BBCodeHandler::getInstance()->getHighlighterMeta();
		$result = [];
		foreach ($meta as $identifier => $data) {
			$result[$identifier] = $data['title'].(strtolower($data['title']) != $identifier ? ' ('.$identifier.')' : '');
		}
		
		asort($result);
		
		return $result;
	}
}
