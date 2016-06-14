<?php
namespace wcf\system\page\handler;

/**
 * Default interface for pages supporting visiblity and outstanding items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
interface IMenuPageHandler {
	/**
	 * Returns the number of outstanding items for this page for display as a badge, optionally
	 * specifying a corresponding object id to limit the scope.
	 * 
	 * @param	integer|null	$objectID	optional page object id
	 * @return	integer		number of outstanding items
	 */
	public function getOutstandingItemCount($objectID = null);
	
	/**
	 * Returns false if this page should be hidden from menus, but does not control the accessibility
	 * of the page itself. The visibility can optionally be scoped to the given object id.
	 * 
	 * @param	integer|null	$objectID	optional page object id
	 * @return	boolean		false if the page should be hidden from menus
	 */
	public function isVisible($objectID = null);
}
