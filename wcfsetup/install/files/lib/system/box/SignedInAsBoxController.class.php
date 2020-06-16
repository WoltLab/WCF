<?php
namespace wcf\system\box;
use wcf\system\WCF;

/**
 * Box that shows the register button.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
 */
class SignedInAsBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		if (WCF::getUser()->userID) {
			$this->content = WCF::getTPL()->fetch('boxSignedInAs', 'wcf', [], true);
		}
	}
}
