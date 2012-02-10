<?php
namespace wcf\action;
use wcf\system\upload\UploadHandler;

/**
 * Default implementation for file uploads using the AJAX-API.
 *
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category 	Community Framework
 */
class AJAXUploadAction extends AJAXProxyAction {
	/**
	 * @see	wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->parameters['__files'] = UploadHandler::getUploadHandler('__files');
	}
}
