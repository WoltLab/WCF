<?php
namespace wcf\system\user\authentication;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Provides a method to check for reauthentication and redirect otherwise.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication
 * @since	5.4
 */
trait TReauthenticationCheck {
	/**
	 * If the current user is in the need for a reauthentication a redirect to
	 * ReauthenticationForm is performed. After successful reauthentication the
	 * user will return to the URL given as `$returnTo`.
	 */
	private function requestReauthentication(string $returnTo): void {
		if (WCF::getSession()->needsReauthentication()) {
			HeaderUtil::redirect(
				LinkHandler::getInstance()->getLink('Reauthentication', [
					'url' => $returnTo,
				])
			);
			exit;
		}
	}
}
