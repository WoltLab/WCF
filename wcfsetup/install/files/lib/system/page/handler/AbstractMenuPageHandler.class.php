<?php
namespace wcf\system\page\handler;

/**
 * Default implementation for pages supporting visiblity and outstanding items.
 * 
 * It is highly recommended to extend this class rather than implementing the interface
 * directly to achieve better upwards-compatibility in case of interface changes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
abstract class AbstractMenuPageHandler implements IMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		return 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		return true;
	}
}
