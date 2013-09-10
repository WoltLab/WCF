<?php
namespace wcf\action;
use wcf\util\JSON;

/**
 * Provides method to send JSON-encoded responses.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
class AbstractAjaxAction extends AbstractAction {
	/**
	 * Sends a JSON-encoded response.
	 * 
	 * @param	array		$data
	 */
	protected function sendJsonResponse(array $data) {
		$json = JSON::encode($data);
		
		// send JSON response
		header('Content-type: application/json');
		echo $json;
		exit;
	}
}
