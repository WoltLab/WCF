<?php
namespace wcf\data;

/**
 * Default interface for message action classes supporting quotes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IMessageQuoteAction {
	/**
	 * Validates parameters to return a parsed template of all associated quotes.
	 */
	public function validateGetRenderedQuotes();
	
	/**
	 * Returns the parsed template for all associated quotes.
	 * 
	 * @return	array
	 */
	public function getRenderedQuotes();
	
	/**
	 * Validates parameters to quote an entire message.
	 */
	public function validateSaveFullQuote();
	
	/**
	 * Quotes an entire message.
	 * 
	 * @return	array
	 */
	public function saveFullQuote();
	
	/**
	 * Validates parameters to save a quote.
	 */
	public function validateSaveQuote();
	
	/**
	 * Saves the quote message and returns the number of stored quotes.
	 * 
	 * @return	array
	 */
	public function saveQuote();
}
