<?php
namespace wcf\system\page\handler;

/**
 * Extends the menu page handler interface by providing additional methods to lookup
 * pages identified by a unique object id.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.page.handler
 * @category	Community Framework
 * @since	2.2
 */
interface ILookupPageHandler extends IMenuPageHandler {
	/**
	 * Performs a search for pages using a query string, returning an array containing
	 * an `objectID => title` relation.
	 * 
	 * @param       string          $searchString   search string
	 * @return      string[]
	 */
	public function lookup($searchString);
}
