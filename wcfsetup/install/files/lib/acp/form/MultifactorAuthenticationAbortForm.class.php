<?php
namespace wcf\acp\form;
use wcf\system\request\LinkHandler;
use wcf\util\HeaderUtil;

/**
 * Aborts the multi-factor authentication process.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	5.4
 */
class MultifactorAuthenticationAbortForm extends \wcf\form\MultifactorAuthenticationAbortForm {
	/**
	 * Returns to the landing page otherwise.
	 */
	protected function performRedirect() {
		HeaderUtil::redirect(
			LinkHandler::getInstance()->getLink('Login')
		);
		exit;
	}
}
