<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\page\IPage;
use wcf\system\cache\builder\UserStatsCacheBuilder;
use wcf\system\WCF;

/**
 * Stats dashboard box.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class StatsSidebarDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * displayed stats
	 * @var	array
	 */
	public $stats = null;
	
	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		$this->stats = UserStatsCacheBuilder::getInstance()->getData();
		
		$this->fetched();
	}
	
	/**
	 * @see	\wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		WCF::getTPL()->assign(array(
			'dashboardStats' => $this->stats
		));
		
		return WCF::getTPL()->fetch('dashboardBoxStatsSidebar');
	}
}
