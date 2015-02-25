<?php
namespace wcf\data;

/**
 * Default interface for actions implementing quick reply with attachment support.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface IAttachmentMessageQuickReplyAction extends IExtendedMessageQuickReplyAction {
	/**
	 * Returns an attachment handler object.
	 * 
	 * @param	\wcf\data\DatabaseObject	$container
	 */
	public function getAttachmentHandler(DatabaseObject $container);
}
