<?php
namespace wcf\system\dashboard\box;
use wcf\system\cache\builder\UserStatsCacheBuilder;
use wcf\system\WCF;

/**
 * Stats dashboard box.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class StatsSidebarDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * @see	wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		WCF::getTPL()->assign(array(
			'dashboardStats' => UserStatsCacheBuilder::getInstance()->getData()
		));
		
		return WCF::getTPL()->fetch('dashboardBoxStatsSidebar');
	}
}
