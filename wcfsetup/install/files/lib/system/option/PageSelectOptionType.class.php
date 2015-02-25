<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\page\PageManager;

/**
 * Option type implementation for selecting pages.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class PageSelectOptionType extends SelectOptionType {
	/**
	 * @see	\wcf\system\option\RadioButtonOptionType::getSelectOptions()
	 */
	protected function getSelectOptions(Option $option) {
		return PageManager::getInstance()->getSelection($option->application);
	}
}
