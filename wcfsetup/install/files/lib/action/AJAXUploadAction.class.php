<?php
namespace wcf\action;
use wcf\system\upload\UploadHandler;
use wcf\util\JSON;

/**
 * Default implementation for file uploads using the AJAX-API.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 */
class AJAXUploadAction extends AJAXProxyAction {
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->parameters['__files'] = UploadHandler::getUploadHandler('__files');
	}
	
	/**
	 * @inheritDoc
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
