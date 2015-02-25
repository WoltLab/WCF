<?php
namespace wcf\data;

/**
 * Default interface for actions implementing quick reply.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface IMessageQuickReplyAction {
	/**
	 * Creates a new message object.
	 * 
	 * @return	\wcf\data\DatabaseObject
	 */
	public function create();
	
	/**
	 * Returns a message list object.
	 * 
	 * @param	\wcf\data\DatabaseObject		$container
	 * @param	integer				$lastMessageTime
	 * @return	\wcf\data\DatabaseObjectList
	 */
	public function getMessageList(DatabaseObject $container, $lastMessageTime);
	
	/**
	 * Returns page no for given container object.
	 * 
	 * @param	\wcf\data\DatabaseObject		$container
	 * @return	array
	 */
	public function getPageNo(DatabaseObject $container);
	
	/**
	 * Returns the redirect url.
	 * 
	 * @param	\wcf\data\DatabaseObject		$container
	 * @param	\wcf\data\DatabaseObject		$message
	 * @return	string
	 */
	public function getRedirectUrl(DatabaseObject $container, DatabaseObject $message);
	
	/**
	 * Validates the message.
	 * 
	 * @param	\wcf\data\DatabaseObject		$container
	 * @param	string				$message
	 */
	public function validateMessage(DatabaseObject $container, $message);
	
	/**
	 * Creates a new message and returns it.
	 * 
	 * @return	array
	 */
	public function quickReply();
	
	/**
	 * Validates the container object for quick reply.
	 * 
	 * @param	\wcf\data\DatabaseObject		$container
	 */
	public function validateContainer(DatabaseObject $container);
	
	/**
	 * Validates parameters for quick reply.
	 */
	public function validateQuickReply();
}
