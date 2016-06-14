<?php
namespace wcf\data\bbcode;
use wcf\system\bbcode\MessageParser;
use wcf\system\bbcode\PreParser;
use wcf\system\exception\UserInputException;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Provides a default message preview action.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Message
 */
class MessagePreviewAction extends BBCodeAction {
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['getMessagePreview'];
		
	/**
	 * Validates parameters for message preview.
	 */
	public function validateGetMessagePreview() {
		if (!isset($this->parameters['data']['message'])) {
			throw new UserInputException('message');
		}
		
		if (!isset($this->parameters['options'])) {
			throw new UserInputException('options');
		}
	}
	
	/**
	 * Returns a rendered message preview.
	 * 
	 * @return	array
	 * @throws	UserInputException
	 */
	public function getMessagePreview() {
		// get options
		$enableBBCodes = (isset($this->parameters['options']['enableBBCodes'])) ? 1 : 0;
		$enableHtml = (isset($this->parameters['options']['enableHtml'])) ? 1 : 0;
		$enableSmilies = (isset($this->parameters['options']['enableSmilies'])) ? 1 : 0;
		$preParse = (isset($this->parameters['options']['preParse'])) ? 1 : 0;
		
		$allowedBBCodesPermission = (isset($this->parameters['allowedBBCodesPermission'])) ? $this->parameters['allowedBBCodesPermission'] : 'user.message.allowedBBCodes';
		
		// validate permissions for options
		if ($enableBBCodes && !WCF::getSession()->getPermission('user.message.canUseBBCodes')) $enableBBCodes = 0;
		if ($enableHtml && !WCF::getSession()->getPermission('user.message.canUseHtml')) $enableHtml = 0;
		if ($enableSmilies && !WCF::getSession()->getPermission('user.message.canUseSmilies')) $enableSmilies = 0;
		
		// check if disallowed bbcode are used
		if ($enableBBCodes && $allowedBBCodesPermission) {
			$disallowedBBCodes = MessageParser::getInstance()->validateBBCodes($this->parameters['data']['message'], ArrayUtil::trim(explode(',', WCF::getSession()->getPermission($allowedBBCodesPermission))));
			if (!empty($disallowedBBCodes)) {
				throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('wcf.message.error.disallowedBBCodes', [
					'disallowedBBCodes' => $disallowedBBCodes
				]));
			}
		}
		
		// get message
		$message = StringUtil::trim($this->parameters['data']['message']);
		
		// get embedded objects
		MessageEmbeddedObjectManager::getInstance()->parseTemporaryMessage($message);
		
		// parse URLs
		if ($preParse && $enableBBCodes) {
			if ($allowedBBCodesPermission) {
				$message = PreParser::getInstance()->parse($message, ArrayUtil::trim(explode(',', WCF::getSession()->getPermission($allowedBBCodesPermission))));
			}
			else {
				$message = PreParser::getInstance()->parse($message);
			}
		}
		
		// parse message
		$preview = MessageParser::getInstance()->parse($message, $enableSmilies, $enableHtml, $enableBBCodes, false);
		
		return [
			'message' => $preview
		];
	}
}
