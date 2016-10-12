<?php
namespace wcf\system\message\quote;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Default implementation for quote handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Message\Quote
 */
abstract class AbstractMessageQuoteHandler extends SingletonFactory implements IMessageQuoteHandler {
	/**
	 * template name
	 * @var	string
	 */
	public $templateName = 'messageQuoteList';
	
	/**
	 * list of quoted message
	 * @var	QuotedMessage[]
	 */
	public $quotedMessages = [];
	
	/**
	 * @inheritDoc
	 */
	public function render(array $data, $supportPaste = false) {
		$messages = $this->getMessages($data);
		$userIDs = $userProfiles = [];
		foreach ($messages as $message) {
			$userID = $message->getUserID();
			if ($userID) {
				$userIDs[] = $userID;
			}
		}
		
		if (!empty($userIDs)) {
			$userIDs = array_unique($userIDs);
			$userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
		}
		
		WCF::getTPL()->assign([
			'messages' => $this->getMessages($data),
			'supportPaste' => $supportPaste,
			'userProfiles' => $userProfiles
		]);
		
		return WCF::getTPL()->fetch($this->templateName);
	}
	
	/**
	 * @inheritDoc
	 */
	public function renderQuotes(array $data, $render = true, $renderAsString = true) {
		$messages = $this->getMessages($data);
		
		$renderedQuotes = [];
		foreach ($messages as $message) {
			foreach ($message as $quoteID => $quote) {
				$quotedMessage = $message->getFullQuote($quoteID);
				if ($render && ($renderAsString && $quotedMessage === null)) {
					$renderedQuotes[] = MessageQuoteManager::getInstance()->renderQuote($message->object, $quote, $renderAsString);
				}
				else {
					$renderedQuotes[] = MessageQuoteManager::getInstance()->renderQuote($message->object, ($quotedMessage === null ? $quote : $quotedMessage), $renderAsString);
				}
			}
		}
		
		return $renderedQuotes;
	}
	
	/**
	 * Returns a list of QuotedMessage objects.
	 * 
	 * @param	mixed[][]	$data
	 * @return	QuotedMessage[]
	 */
	abstract protected function getMessages(array $data);
}
