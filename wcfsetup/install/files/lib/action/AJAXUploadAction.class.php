<?php
namespace wcf\action;
use wcf\system\upload\UploadHandler;
use wcf\util\JSON;

/**
 * Default implementation for file uploads using the AJAX-API.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
class AJAXUploadAction extends AJAXProxyAction {
	/**
	 * @see	\wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->parameters['__files'] = UploadHandler::getUploadHandler('__files');
	}
	
	/**
	 * @see	\wcf\action\AJAXInvokeAction::sendResponse()
	 */
	protected function sendResponse() {
		if (!isset($_POST['isFallback'])) {
			parent::sendResponse();
		}
		
		// IE9 is mad if iframe response is application/json
		header('Content-type: text/plain');
		echo JSON::encode($this->response);
		exit;
	}
}
