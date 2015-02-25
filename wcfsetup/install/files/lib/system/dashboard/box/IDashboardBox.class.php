<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\page\IPage;

/**
 * Default interface for dashboard boxes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
interface IDashboardBox {
	/**
	 * Initializes this box.
	 * 
	 * @param	\wcf\data\dashboard\box\DashboardBox	$box
	 * @param	\wcf\page\IPage				$page
	 */
	public function init(DashboardBox $box, IPage $page);
	
	/**
	 * Returns parsed box template.
	 * 
	 * @return	string
	 */
	public function getTemplate();
}
