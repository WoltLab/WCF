<?php
namespace wcf\system\dashboard\box;
use wcf\system\WCF;

/**
 * Dashboard box for registration button.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class RegisterButtonDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * @see	wcf\system\dashboard\box\AbstractContentDashboardBox::$templateName
	 */
	public $templateName = 'dashboardBoxRegisterButton';
	
	/**
	 * @see	wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		return ((!WCF::getUser()->userID && !REGISTER_DISABLED) ? true : false);
	}
}
