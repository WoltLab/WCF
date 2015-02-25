<?php
namespace wcf\system\dashboard\box;
use wcf\system\WCF;

/**
 * 'Signed in as' dashboard box.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class SignedInAsDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * @see	\wcf\system\dashboard\box\AbstractContentDashboardBox::$templateName
	 */
	public $templateName = 'dashboardBoxSignedInAs';
	
	/**
	 * @see	\wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		return (WCF::getUser()->userID ? true : false);
	}
}
