<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\smiley\SmileyCache;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Option type implementation for message.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class MessageOptionType extends TextareaOptionType {
	/**
	 * @var HtmlInputProcessor
	 */
	protected $htmlInputProcessor;
	
	/**
	 * @inheritDoc
	 */
	/*public function getData(Option $option, $newValue) {
		if ($option->disallowedbbcodepermission) {
			BBCodeHandler::getInstance()->setDisallowedBBCodes(explode(',', ArrayUtil::trim(WCF::getSession()->getPermission($option->disallowedbbcodepermission))));
		}
		
		$this->htmlInputProcessor = new HtmlInputProcessor();
		$this->htmlInputProcessor->process($newValue, 'com.woltlab.wcf.invalid', 0);
		
		return parent::getData($option, $this->htmlInputProcessor->getHtml());
	}*/
	
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		$disallowedBBCodes = [];
		if ($option->disallowedbbcodepermission) {
			$disallowedBBCodes = explode(',', WCF::getSession()->getPermission($option->disallowedbbcodepermission));
		}
		BBCodeHandler::getInstance()->setDisallowedBBCodes($disallowedBBCodes);
		
		WCF::getTPL()->assign([
			'defaultSmilies' => SmileyCache::getInstance()->getCategorySmilies(),
			'option' => $option,
			'value' => $value
		]);
		
		return WCF::getTPL()->fetch('messageOptionType');
	}
	
	/**
	 * @inheritDoc
	 */
	/*public function validate(Option $option, $newValue) {
		parent::validate($option, $newValue);
		
		if ($option->disallowedbbcodepermission) {
			$disallowedBBCodes = $this->htmlInputProcessor->validate();
			
			if (!empty($disallowedBBCodes)) {
				WCF::getTPL()->assign('disallowedBBCodes', $disallowedBBCodes);
				throw new UserInputException($option->optionName, 'disallowedBBCodes');
			}
		}
	}*/
}
