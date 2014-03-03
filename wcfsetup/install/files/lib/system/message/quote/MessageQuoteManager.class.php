<?php
namespace wcf\system\message\quote;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\IMessage;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Manages message quotes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message.quote
 * @category	Community Framework
 */
class MessageQuoteManager extends SingletonFactory {
	/**
	 * current object ids
	 * @var	array<integer>
	 */
	protected $objectIDs = array();
	
	/**
	 * current object type name
	 * @var	string
	 */
	protected $objectType = '';
	
	/**
	 * list of object types
	 * @var	array<\wcf\data\object\type\ObjectType>
	 */
	protected $objectTypes = array();
	
	/**
	 * primary application's package id
	 * @var	integer
	 */
	protected $packageID = 0;
	
	/**
	 * list of stored quotes
	 * @var	array<array>
	 */
	protected $quotes = array();
	
	/**
	 * list of quote messages by quote id
	 * @var	array<string>
	 */
	protected $quoteData = array();
	
	/**
	 * message id for quoting
	 * @var	integer
	 */
	protected $quoteMessageID = 0;
	
	/**
	 * list of quote ids to be removed
	 * @var	array<string>
	 */
	protected $removeQuoteIDs = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->packageID = ApplicationHandler::getInstance()->getPrimaryApplication()->packageID;
		
		// load stored quotes from session
		$messageQuotes = WCF::getSession()->getVar('__messageQuotes'.$this->packageID);
		if (is_array($messageQuotes)) {
			$this->quotes = (isset($messageQuotes['quotes'])) ? $messageQuotes['quotes'] : array();
			$this->quoteData = (isset($messageQuotes['quoteData'])) ? $messageQuotes['quoteData'] : array();
			$this->removeQuoteIDs = (isset($messageQuotes['removeQuoteIDs'])) ? $messageQuotes['removeQuoteIDs'] : array();
		}
		
		// load object types
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.message.quote');
		foreach ($objectTypes as $objectType) {
			$this->objectTypes[$objectType->objectType] = $objectType;
		}
	}
	
	/**
	 * Adds a quote unless it is already stored. If you want to quote a whole
	 * message while maintaing the original markup, pass $obj->getExcerpt() for
	 * $message and $obj->getMessage() for $fullQuote.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$parentObjectID
	 * @param	integer		$objectID
	 * @param	string		$message
	 * @param	string		$fullQuote
	 * @param	boolean
	 */
	public function addQuote($objectType, $parentObjectID, $objectID, $message, $fullQuote = '') {
		if (!isset($this->objectTypes[$objectType])) {
			throw new SystemException("Object type '".$objectType."' is unknown");
		}
		
		if (!isset($this->quotes[$objectType])) {
			$this->quotes[$objectType] = array();
		}
		
		if (!isset($this->quotes[$objectType][$objectID])) {
			$this->quotes[$objectType][$objectID] = array();
		}
		
		$quoteID = $this->getQuoteID($objectType, $objectID, $message, $fullQuote);
		if (!isset($this->quotes[$objectType][$objectID][$quoteID])) {
			$this->quotes[$objectType][$objectID][$quoteID] = 0;
			$this->quoteData[$quoteID] = $message;
			
			// save parent object id
			
			if (!isset($this->quoteData['parents'])) {
				$this->quoteData['parents'] = array();
			}
			
			if (!isset($this->quoteData['parents'][$objectType])) {
				$this->quoteData['parents'][$objectType] = array();
			}
			
			if (!isset($this->quoteData['parents'][$objectType][$parentObjectID])) {
				$this->quoteData['parents'][$objectType][$parentObjectID] = array();
			}
			
			$this->quoteData['parents'][$objectType][$parentObjectID][] = $objectID;
			$this->quoteData[$quoteID.'_pID'] = $parentObjectID;
			
			if (!empty($fullQuote)) {
				$this->quotes[$objectType][$objectID][$quoteID] = 1;
				$this->quoteData[$quoteID.'_fq'] = $fullQuote;
			}
			
			$this->updateSession();
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the quote id for given quote.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	string		$message
	 * @param	string		$fullQuote
	 * @return	string
	 */
	public function getQuoteID($objectType, $objectID, $message, $fullQuote = '') {
		return substr(sha1($objectType.'|'.$objectID.'|'.$message.'|'.$fullQuote), 0, 8);
	}
	
	/**
	 * Removes a quote from storage.
	 * 
	 * @param	string		$quoteID
	 */
	public function removeQuote($quoteID) {
		if (!isset($this->quoteData[$quoteID])) {
			return false;
		}
		
		foreach ($this->quotes as $objectType => $objectIDs) {
			foreach ($objectIDs as $objectID => $quoteIDs) {
				foreach ($quoteIDs as $qID => $isFullQuote) {
					if ($qID == $quoteID) {
						unset($this->quotes[$objectType][$objectID][$qID]);
						
						// clean-up structure
						if (empty($this->quotes[$objectType][$objectID])) {
							unset($this->quotes[$objectType][$objectID]);
							
							if (empty($this->quotes[$objectType])) {
								unset($this->quotes[$objectType]);
							}
						}
						
						unset($this->quoteData[$quoteID]);
						if ($isFullQuote) {
							unset($this->quoteData[$quoteID.'_fq']);
						}
						
						// remove parent object id reference
						if (isset($this->quoteData[$quoteID.'_pID'])) {
							$parentObjectID = $this->quoteData[$quoteID.'_pID'];
							if (!isset($this->quotes[$objectType][$objectID])) {
								if (isset($this->quoteData['parents'][$objectType][$parentObjectID][$objectID])) {
									unset($this->quoteData['parents'][$objectType][$parentObjectID][$objectID]);
									
									// cleanup
									if (empty($this->quoteData['parents'][$objectType][$parentObjectID])) {
										unset($this->quoteData['parents'][$objectType][$parentObjectID]);
									
										if (empty($this->quoteData['parents'][$objectType])) {
											unset($this->quoteData['parents'][$objectType]);
												
											if (empty($this->quoteData['parents'])) {
												unset($this->quoteData['parents']);
											}
										}
									}
								}
							}
						}
						
						$this->updateSession();
						
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Returns a list of quotes.
	 * 
	 * @param	boolean		supportPaste
	 */
	public function getQuotes($supportPaste = false) {
		$template = '';
		
		foreach ($this->quotes as $objectType => $objectData) {
			$quoteHandler = call_user_func(array($this->objectTypes[$objectType]->className, 'getInstance'));
			$template .= $quoteHandler->render($objectData, $supportPaste);
		}
		
		return $template;
	}
	
	/**
	 * Returns a list of quotes by object type and id.
	 * 
	 * @param	string		$objectType
	 * @param	array<integer>	$objectIDs
	 * @param	boolean		$markForRemoval
	 * @return	array<string>
	 */
	public function getQuotesByObjectIDs($objectType, array $objectIDs, $markForRemoval = true) {
		if (!isset($this->quotes[$objectType])) {
			return array();
		}
		
		$data = array();
		$removeQuoteIDs = array();
		foreach ($this->quotes[$objectType] as $objectID => $quoteIDs) {
			if (in_array($objectID, $objectIDs)) {
				$data[$objectID] = $quoteIDs;
				
				// mark quotes for removal
				if ($markForRemoval) {
					$removeQuoteIDs = array_merge($removeQuoteIDs, array_keys($quoteIDs));
				}
			}
		}
		
		// no quotes found
		if (empty($data)) {
			return array();
		}
		
		// mark quotes for removal
		if (!empty($removeQuoteIDs)) {
			$this->markQuotesForRemoval($removeQuoteIDs);
		}
		
		$quoteHandler = call_user_func(array($this->objectTypes[$objectType]->className, 'getInstance'));
		return $quoteHandler->renderQuotes($data);
	}
	
	/**
	 * Returns a list of quotes by object type and parent object id.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$parentObjectID
	 * @param	boolean		$markForRemoval
	 * @return	array<string>
	 */
	public function getQuotesByParentObjectID($objectType, $parentObjectID, $markForRemoval = true) {
		if (!isset($this->quoteData['parents'][$objectType][$parentObjectID])) {
			return array();
		}
		
		$data = array();
		$removeQuoteIDs = array();
		foreach ($this->quoteData['parents'][$objectType][$parentObjectID] as $objectID) {
			if (isset($this->quotes[$objectType][$objectID])) {
				$data[$objectID] = $this->quotes[$objectType][$objectID];
				
				// mark quotes for removal
				if ($markForRemoval) {
					$removeQuoteIDs = array_merge($removeQuoteIDs, array_keys($data[$objectID]));
				}
			}
		}
		
		// no quotes found
		if (empty($data)) {
			return array();
		}
		
		// mark quotes for removal
		if (!empty($removeQuoteIDs)) {
			$this->markQuotesForRemoval($removeQuoteIDs);
		}
		
		$quoteHandler = call_user_func(array($this->objectTypes[$objectType]->className, 'getInstance'));
		return $quoteHandler->renderQuotes($data, false);
	}
	
	/**
	 * Returns a quote by id.
	 * 
	 * @param	string		$quoteID
	 * @param	boolean		$useFullQuote
	 * @return	string
	 */
	public function getQuote($quoteID, $useFullQuote = true) {
		if ($useFullQuote && isset($this->quoteData[$quoteID.'_fq'])) {
			return $this->quoteData[$quoteID.'_fq'];
		}
		else if (isset($this->quoteData[$quoteID])) {
			return $this->quoteData[$quoteID];
		}
		
		return null;
	}
	
	/**
	 * Returns the object id by quote id.
	 * 
	 * @param	string		$quoteID
	 * @return	integer
	 */
	public function getObjectID($quoteID) {
		if (isset($this->quoteData[$quoteID])) {
			foreach ($this->quotes as $objectIDs) {
				foreach ($objectIDs as $objectID => $quoteIDs) {
					if (isset($quoteIDs[$quoteID])) {
						return $objectID;
					}
				}
			}
		}
		
		return null;
	}
	
	/**
	 * Marks quote ids for removal.
	 * 
	 * @param	array<string>	$quoteIDs
	 */
	public function markQuotesForRemoval(array $quoteIDs) {
		foreach ($quoteIDs as $index => $quoteID) {
			if (!isset($this->quoteData[$quoteID]) || in_array($quoteID, $this->removeQuoteIDs)) {
				unset($quoteIDs[$index]);
			}
		}
		
		if (!empty($quoteIDs)) {
			$this->removeQuoteIDs = array_merge($this->removeQuoteIDs, $quoteIDs);
			$this->updateSession();
		}
	}
	
	/**
	 * Renders a quote for given message.
	 * 
	 * @param	\wcf\data\IMessage	$message
	 * @param	string			$text
	 * @return	string
	 */
	public function renderQuote(IMessage $message, $text) {
		$escapedUsername = str_replace(array("\\", "'"), array("\\\\", "\'"), $message->getUsername());
		$escapedLink = str_replace(array("\\", "'"), array("\\\\", "\'"), $message->getLink());
		
		return "[quote='".$escapedUsername."','".$escapedLink."']".$text."[/quote]";
	}
	
	/**
	 * Removes quotes marked for removal.
	 */
	public function removeMarkedQuotes() {
		if (!empty($this->removeQuoteIDs)) {
			foreach ($this->removeQuoteIDs as $quoteID) {
				$this->removeQuote($quoteID);
			}
			
			// reset list of quote ids marked for removal
			$this->removeQuoteIDs = array();
			
			$this->updateSession();
		}
	}
	
	/**
	 * Returns the number of stored quotes.
	 * 
	 * @return	integer
	 */
	public function countQuotes() {
		$count = 0;
		foreach ($this->quoteData as $quoteID => $quote) {
			if (strlen($quoteID) == 8) {
				$count++;
			}
		}
		
		return $count;
	}
	
	/**
	 * Returns a list of full quotes by object id for given object types.
	 * 
	 * @param	array<string>		$objectTypes
	 * @return	array<array>
	 */
	public function getFullQuoteObjectIDs(array $objectTypes) {
		$objectIDs = array();
		
		foreach ($objectTypes as $objectType) {
			if (!isset($this->objectTypes[$objectType])) {
				throw new SystemException("Object type '".$objectType."' is unknown");
			}
			
			$objectIDs[$objectType] = array();
			if (isset($this->quotes[$objectType])) {
				foreach ($this->quotes[$objectType] as $objectID => $quotes) {
					foreach ($quotes as $quoteID => $isFullQuote) {
						if ($isFullQuote) {
							$objectIDs[$objectType][] = $objectID;
							break;
						}
					}
				}
			}
		}
		
		return $objectIDs;
	}
	
	/**
	 * Sets object type and object ids.
	 * 
	 * @param	string		$objectType
	 * @param	array<integer>	$objectIDs
	 */
	public function initObjects($objectType, array $objectIDs) {
		if (!isset($this->objectTypes[$objectType])) {
			throw new SystemException("Object type '".$objectType."' is unknown");
		}
		
		$this->objectIDs = ArrayUtil::toIntegerArray($objectIDs);
		$this->objectType = $objectType;
	}
	
	/**
	 * Reads the quote message id.
	 */
	public function readParameters() {
		if (isset($_REQUEST['quoteMessageID'])) $this->quoteMessageID = intval($_REQUEST['quoteMessageID']);
	}
	
	/**
	 * Reads a list of quote ids to remove.
	 */
	public function readFormParameters() {
		if (isset($_REQUEST['__removeQuoteIDs']) && is_array($_REQUEST['__removeQuoteIDs'])) {
			$quoteIDs = ArrayUtil::trim($_REQUEST['__removeQuoteIDs']);
			foreach ($quoteIDs as $index => $quoteID) {
				if (!isset($this->quoteData[$quoteID])) {
					unset($quoteIDs[$index]);
				}
			}
			
			if (!empty($quoteIDs)) {
				$this->removeQuoteIDs = array_merge($this->removeQuoteIDs, $quoteIDs);
			}
		}
	}
	
	/**
	 * Removes quotes after saving current message.
	 */
	public function saved() {
		$this->removeMarkedQuotes();
	}
	
	/**
	 * Assigns variables on page load.
	 */
	public function assignVariables() {
		$fullQuoteObjectIDs = array();
		if (!empty($this->objectType) && !empty($this->objectIDs) && isset($this->quotes[$this->objectType])) {
			foreach ($this->quotes[$this->objectType] as $objectID => $quotes) {
				if (!in_array($objectID, $this->objectIDs)) {
					continue;
				}
				
				foreach ($quotes as $isFullQuote) {
					if ($isFullQuote) {
						$fullQuoteObjectIDs[] = $objectID;
						break;
					}
				}
			}
		}
		
		WCF::getTPL()->assign(array(
			'__quoteCount' => $this->countQuotes(),
			'__quoteFullQuote' => $fullQuoteObjectIDs,
			'__quoteRemove' => $this->removeQuoteIDs
		));
	}
	
	/**
	 * Returns quote message id.
	 * 
	 * @return	integer
	 */
	public function getQuoteMessageID() {
		return $this->quoteMessageID;
	}
	
	/**
	 * Removes orphaned quote ids
	 * 
	 * @param	array<integer>		$quoteIDs
	 */
	public function removeOrphanedQuotes(array $quoteIDs) {
		foreach ($quoteIDs as $quoteID) {
			$this->removeQuote($quoteID);
		}
		
		$this->updateSession();
	}
	
	/**
	 * Updates data stored in session,
	 */
	protected function updateSession() {
		WCF::getSession()->register('__messageQuotes'.$this->packageID, array(
			'quotes' => $this->quotes,
			'quoteData' => $this->quoteData,
			'removeQuoteIDs' => $this->removeQuoteIDs
		));
	}
}
