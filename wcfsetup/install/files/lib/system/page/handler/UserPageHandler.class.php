<?php
namespace wcf\system\page\handler;

/**
 * Menu page handler for the user profile page.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
class UserPageHandler extends AbstractMenuPageHandler implements IOnlineLocationPageHandler {
	use TUserOnlineLocationPageHandler;
}
