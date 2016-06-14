<?php
namespace wcf\data;

/**
 * Default interface for actions implementing quick reply with attachment support.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IAttachmentMessageQuickReplyAction extends IExtendedMessageQuickReplyAction {
	/**
	 * Returns an attachment handler object.
	 * 
	 * @param	\wcf\data\DatabaseObject	$container
	 */
	public function getAttachmentHandler(DatabaseObject $container);
}
