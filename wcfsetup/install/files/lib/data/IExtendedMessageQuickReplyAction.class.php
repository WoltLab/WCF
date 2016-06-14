<?php
namespace wcf\data;

/**
 * Default interface for actions implementing quick reply with extended mode.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IExtendedMessageQuickReplyAction extends IMessageQuickReplyAction {
	/**
	 * Saves message and jumps to extended mode.
	 * 
	 * @return	array
	 */
	public function jumpToExtended();
	
	/**
	 * Validates parameters to jump to extended mode.
	 */
	public function validateJumpToExtended();
}
