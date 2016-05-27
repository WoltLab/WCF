<?php
namespace wcf\system\box;
use wcf\system\WCF;

/**
 * Box that shows the register button.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 * @since	2.2
 */
class RegisterButtonBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		if (!WCF::getUser()->userID && !REGISTER_DISABLED) {
			$this->content = WCF::getTPL()->fetch('boxRegisterButton');
		}
	}
}
