<?php
namespace wcf\system\option;
use wcf\data\bbcode\BBCodeCache;
use wcf\data\option\Option;
use wcf\data\smiley\SmileyCache;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\bbcode\BBCodeParser;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Option type implementation for message.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class MessageOptionType extends TextareaOptionType {
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		$newValue = StringUtil::trim($newValue);
		
		return parent::getData($option, $newValue);
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		$allowedBBCodes = array();
		if ($option->allowedbbcodepermission) {
			$allowedBBCodes = explode(',', WCF::getSession()->getPermission($option->allowedbbcodepermission));
		}
		else {
			$allowedBBCodes = array_keys(BBCodeCache::getInstance()->getBBCodes());
		}
		BBCodeHandler::getInstance()->setAllowedBBCodes($allowedBBCodes);
		
		WCF::getTPL()->assign(array(
			'defaultSmilies' => SmileyCache::getInstance()->getCategorySmilies(),
			'option' => $option,
			'value' => $value
		));
		
		return WCF::getTPL()->fetch('messageOptionType');
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		parent::validate($option, $newValue);
		
		if ($option->allowedbbcodepermission) {
			$disallowedBBCodes = BBCodeParser::getInstance()->validateBBCodes($newValue, explode(',', ArrayUtil::trim(WCF::getSession()->getPermission($option->allowedbbcodepermission))));
			if (!empty($disallowedBBCodes)) {
				WCF::getTPL()->assign('disallowedBBCodes', $disallowedBBCodes);
				throw new UserInputException($option->optionName, 'disallowedBBCodes');
			}
		}
	}
}
