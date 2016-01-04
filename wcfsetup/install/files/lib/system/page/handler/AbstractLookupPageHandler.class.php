<?php
namespace wcf\system\page\handler;

/**
 * Default implementation for menu page handlers with additional methods to lookup pages
 * identified by a unique object id.
 * 
 * It is highly recommended to extend this class rather than implementing the interface
 * directly to achieve better upwards-compatibility in case of interface changes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.page.handler
 * @category	Community Framework
 * @since	2.2
 */
abstract class AbstractLookupPageHandler implements ILookupPageHandler {
	/**
	 * @inheritDoc
	 */
	public function lookup($searchString) {
		return [];
	}
}
