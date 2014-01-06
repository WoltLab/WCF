<?php
namespace wcf\system\message\quote;
use wcf\data\user\UserProfile;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Default implementation for quote handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message.quote
 * @category	Community Framework
 */
abstract class AbstractMessageQuoteHandler extends SingletonFactory implements IMessageQuoteHandler {
	/**
	 * template name
	 * @var	string
	 */
	public $templateName = 'messageQuoteList';
	
	/**
	 * list of quoted message
	 * @var	array<\wcf\system\message\quote\QuotedMessage>
	 */
	public $quotedMessages = array();
	
	/**
	 * @see	\wcf\system\message\quote\IMessageQuoteHandler::render()
	 */
	public function render(array $data, $supportPaste = false) {
		$messages = $this->getMessages($data);
		$userIDs = $userProfiles = array();
		foreach ($messages as $message) {
			$userID = $message->getUserID();
			if ($userID) {
				$userIDs[] = $userID;
			}
		}
		
		if (!empty($userIDs)) {
			$userIDs = array_unique($userIDs);
			$userProfiles = UserProfile::getUserProfiles($userIDs);
		}
		
		WCF::getTPL()->assign(array(
			'messages' => $this->getMessages($data),
			'supportPaste' => $supportPaste,
			'userProfiles' => $userProfiles
		));
		
		return WCF::getTPL()->fetch($this->templateName);
	}
	
	/**
	 * @see	\wcf\system\message\quote\IMessageQuoteHandler::renderQuotes()
	 */
	public function renderQuotes(array $data, $render = true) {
		$messages = $this->getMessages($data);
		
		$renderedQuotes = array();
		foreach ($messages as $message) {
			foreach ($message as $quoteID => $quote) {
				if ($render) {
					$renderedQuotes[] = MessageQuoteManager::getInstance()->renderQuote($message->object, $quote);
				}
				else {
					$quotedMessage = $message->getFullQuote($quoteID);
					$renderedQuotes[] = MessageQuoteManager::getInstance()->renderQuote($message->object, ($quotedMessage === null ? $quote : $quotedMessage));
				}
			}
		}
		
		return $renderedQuotes;
	}
	
	/**
	 * Returns a list of QuotedMessage objects.
	 * 
	 * @param	array<array>	$data
	 * @return	array<\wcf\system\message\quote\QuotedMessage>
	 */
	abstract protected function getMessages(array $data);
}
