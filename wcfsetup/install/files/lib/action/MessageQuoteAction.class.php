<?php
namespace wcf\action;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\message\quote\MessageQuoteManager;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Handles message quotes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 */
class MessageQuoteAction extends AJAXProxyAction {
	/**
	 * indicates if the WCF.Message.Quote.Manager object requesting data has any
	 * quote handlers which require updated object ids of full quotes
	 * @var	integer
	 */
	public $_getFullQuoteObjectIDs = false;
	
	/**
	 * list of quote ids
	 * @var	string[]
	 */
	public $quoteIDs = [];
	
	/**
	 * list of object types
	 * @var	string[]
	 */
	public $objectTypes = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		AbstractSecureAction::readParameters();
		
		if (isset($_POST['actionName'])) $this->actionName = StringUtil::trim($_POST['actionName']);
		if (isset($_POST['getFullQuoteObjectIDs'])) $this->_getFullQuoteObjectIDs = intval($_POST['getFullQuoteObjectIDs']);
		if (isset($_POST['objectTypes']) && is_array($_POST['objectTypes'])) $this->objectTypes = ArrayUtil::trim($_POST['objectTypes']);
		if (isset($_POST['quoteIDs'])) {
			$this->quoteIDs = ArrayUtil::trim($_POST['quoteIDs']);
			
			// validate quote ids
			foreach ($this->quoteIDs as $key => $quoteID) {
				if (MessageQuoteManager::getInstance()->getQuote($quoteID) === null) {
					unset($this->quoteIDs[$key]);
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		AbstractAction::execute();
		
		$returnValues = null;
		switch ($this->actionName) {
			case 'count':
				$returnValues = [
					'count' => $this->count()
				];
			break;
			
			case 'getQuotes':
				$returnValues = [
					'template' => $this->getQuotes()
				];
			break;
			
			case 'markForRemoval':
				$this->markForRemoval();
			break;
			
			case 'remove':
				$returnValues = [
					'count' => $this->remove()
				];
			break;
			
			case 'removeMarkedQuotes':
				$returnValues = [
					'count' => $this->removeMarkedQuotes()
				];
			break;
			
			default:
				throw new SystemException("Unknown action '".$this->actionName."'");
			break;
		}
		
		if (is_array($returnValues) && $this->_getFullQuoteObjectIDs) {
			$returnValues['fullQuoteObjectIDs'] = $this->getFullQuoteObjectIDs();
		}
		
		$this->executed();
		
		// force session update
		WCF::getSession()->update();
		WCF::getSession()->disableUpdate();
		
		if ($returnValues !== null) {
			// send JSON-encoded response
			header('Content-type: application/json');
			echo JSON::encode($returnValues);
		}
		
		exit;
	}
	
	/**
	 * Returns the count of stored quotes.
	 * 
	 * @return	integer
	 */
	protected function count() {
		return MessageQuoteManager::getInstance()->countQuotes();
	}
	
	/**
	 * Returns the quote list template.
	 * 
	 * @return	string
	 */
	protected function getQuotes() {
		$supportPaste = (isset($_POST['supportPaste'])) ? (bool)$_POST['supportPaste'] : false;
		
		return MessageQuoteManager::getInstance()->getQuotes($supportPaste);
	}
	
	/**
	 * Marks quotes for removal.
	 */
	protected function markForRemoval() {
		if (!empty($this->quoteIDs)) {
			MessageQuoteManager::getInstance()->markQuotesForRemoval($this->quoteIDs);
		}
	}
	
	/**
	 * Removes a list of quotes from storage and returns the remaining count.
	 * 
	 * @return	integer
	 * @throws	SystemException
	 * @throws	UserInputException
	 */
	protected function remove() {
		if (empty($this->quoteIDs)) {
			throw new UserInputException('quoteIDs');
		}
		
		foreach ($this->quoteIDs as $quoteID) {
			if (!MessageQuoteManager::getInstance()->removeQuote($quoteID)) {
				throw new SystemException("Unable to remove quote identified by '".$quoteID."'");
			}
		}
		
		return $this->count();
	}
	
	/**
	 * Removes all quotes marked for removal and returns the remaining count.
	 * 
	 * @return	integer
	 */
	protected function removeMarkedQuotes() {
		MessageQuoteManager::getInstance()->removeMarkedQuotes();
		
		return $this->count();
	}
	
	/**
	 * Returns a list of full quotes by object ids for given object types.
	 * 
	 * @return	array<array>
	 * @throws	UserInputException
	 */
	protected function getFullQuoteObjectIDs() {
		if (empty($this->objectTypes)) {
			throw new UserInputException('objectTypes');
		}
		
		try {
			return MessageQuoteManager::getInstance()->getFullQuoteObjectIDs($this->objectTypes);
		}
		catch (SystemException $e) {
			throw new UserInputException('objectTypes');
		}
	}
}
