<?php
namespace wcf\system\dashboard\box;

/**
 * Default implementation for dashboard boxes displayed within the sidebar container.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
abstract class AbstractSidebarDashboardBox extends AbstractContentDashboardBox {
	/**
	 * @see	\wcf\system\dashboard\box\AbstractDashboardBoxContent::$templateName
	 */
	public $templateName = 'dashboardBoxSidebar';
}
