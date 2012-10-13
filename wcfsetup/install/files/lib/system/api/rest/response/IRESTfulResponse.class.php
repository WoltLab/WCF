<?php
namespace wcf\system\api\rest\response;

/**
 * Interface for all rest'able database objects.
 *
 * @author		Jeffrey Reichardt
 * @copyright	2001-2012 WoltLab GmbH
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.api.rest.response
 * @category 	Community Framework
 */
interface IRESTfulResponse {
	/**
	 * Returns a list of fields for responsing data.
	 *
	 * @return array<string>
	 */
	public function getResponseFields();
}
