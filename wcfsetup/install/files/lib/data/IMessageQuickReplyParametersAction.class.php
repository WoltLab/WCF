<?php
namespace wcf\data;

/**
 * Default interface for actions implementing quick reply with parameter validation.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Data
 */
interface IMessageQuickReplyParametersAction extends IMessageQuickReplyAction {
	/**
	 * Returns the list of allowed data parameters for the 'quickReply' action. The
	 * 'message' key is permitted by default.
	 * 
	 * @return      string[]
	 */
	public function getAllowedQuickReplyParameters();
}
