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
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
abstract class AbstractLookupPageHandler extends AbstractMenuPageHandler implements ILookupPageHandler {}
