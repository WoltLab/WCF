<?php
namespace wcf\system\box;
use wcf\system\cache\builder\UserStatsCacheBuilder;
use wcf\system\WCF;

/**
 * Box that shows global statistics.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
 */
class StatisticsBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		if (WCF::getSession()->getPermission('user.profile.canViewStatistics')) {
			$this->content = WCF::getTPL()->fetch('boxStatistics', 'wcf', ['statistics' => UserStatsCacheBuilder::getInstance()->getData()], true);
		}
	}
}
