<?php
namespace wcf\system\breadcrumb;

/**
 * Interface for breadcrumb provider.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Breadcrumb
 * @deprecated  3.0
 */
interface IBreadcrumbProvider {
	/**
	 * Returns a Breadcrumb object.
	 * 
	 * @return	Breadcrumb
	 */
	public function getBreadcrumb();
}
