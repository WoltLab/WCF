<?php
namespace wcf\system\box;
use wcf\system\cache\builder\UserStatsCacheBuilder;
use wcf\system\WCF;

/**
 * Box that shows global statistics.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 * @since	2.2
 */
class StatisticsBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		WCF::getTPL()->assign(array(
			'statistics' => UserStatsCacheBuilder::getInstance()->getData()
		));
		
		$this->content = WCF::getTPL()->fetch('boxStatistics');
	}
}
