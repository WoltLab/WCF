<?php
namespace wcf\system\page\handler;

/**
 * Extends the menu page handler interface by providing additional methods to lookup
 * pages identified by a unique object id.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
interface ILookupPageHandler extends IMenuPageHandler {
	/**
	 * Returns the link for a page with an object id.
	 * 
	 * @param	integer		$objectID	page object id
	 * @return	string		page url
	 */
	public function getLink($objectID);
	
	/**
	 * Returns true if provided object id exists and is valid.
	 * 
	 * @param	integer		$objectID	page object id
	 * @return	boolean		true if object id is valid
	 */
	public function isValid($objectID);
	
	/**
	 * Performs a search for pages using a query string, returning an array containing
	 * an `objectID => title` relation.
	 * 
	 * @param	string		$searchString	search string
	 * @return	string[]
	 */
	public function lookup($searchString);
}
