<?php
namespace wcf\system\breadcrumb;

/**
 * Interface for breadcrumb provider.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Breadcrumb
 */
interface IBreadcrumbProvider {
	/**
	 * Returns a Breadcrumb object.
	 * 
	 * @return	\wcf\system\breadcrumb\Breadcrumb
	 */
	public function getBreadcrumb();
}
