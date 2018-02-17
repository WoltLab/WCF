<?php
namespace wcf\data\bbcode;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Provides a default message preview action.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
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
		$this->readString('message', false, 'data');
		$this->readString('messageObjectType');
		$this->readInteger('messageObjectID', true);
	}
	
	/**
	 * Returns a rendered message preview.
	 * 
	 * @return	array
	 * @throws	UserInputException
	 */
	public function getMessagePreview() {
		// set disallowed bbcodes first to ensure proper parsing
		$disallowedBBCodesPermission = isset($this->parameters['disallowedBBCodesPermission']) ? $this->parameters['disallowedBBCodesPermission'] : 'user.message.disallowedBBCodes';
		if ($disallowedBBCodesPermission) {
			BBCodeHandler::getInstance()->setDisallowedBBCodes(ArrayUtil::trim(explode(',', WCF::getSession()->getPermission($disallowedBBCodesPermission))));
		}
		
		$htmlInputProcessor = new HtmlInputProcessor();
		$htmlInputProcessor->process($this->parameters['data']['message'], $this->parameters['messageObjectType'], $this->parameters['messageObjectID']);
		
		// check if disallowed bbcode are used
		if ($disallowedBBCodesPermission) {
			$disallowedBBCodes = $htmlInputProcessor->validate();
			if (!empty($disallowedBBCodes)) {
				throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('wcf.message.error.disallowedBBCodes', [
					'disallowedBBCodes' => $disallowedBBCodes
				]));
			}
		}
		
		MessageEmbeddedObjectManager::getInstance()->registerTemporaryMessage($htmlInputProcessor);
		
		$htmlOutputProcessor = new HtmlOutputProcessor();
		$htmlOutputProcessor->process($htmlInputProcessor->getHtml(), $this->parameters['messageObjectType'], $this->parameters['messageObjectID']);
		
		return [
			'message' => $htmlOutputProcessor->getHtml(),
			'raw' => $htmlInputProcessor->getHtml()
		];
	}
}
