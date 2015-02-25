<?php
namespace wcf\system\breadcrumb;

/**
 * Interface for breadcrumb provider.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.breadcrumb
 * @category	Community Framework
 */
interface IBreadcrumbProvider {
	/**
	 * Returns a Breadcrumb object.
	 * 
	 * @return	\wcf\system\breadcrumb\Breadcrumb
	 */
	public function getBreadcrumb();
}
