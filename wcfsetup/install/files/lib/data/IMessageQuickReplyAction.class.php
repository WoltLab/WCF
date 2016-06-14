<?php
namespace wcf\data;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Default interface for actions implementing quick reply.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IMessageQuickReplyAction {
	/**
	 * Creates a new message object.
	 * 
	 * @return	DatabaseObject
	 */
	public function create();
	
	/**
	 * Returns the current html input processor or a new one if `$message` is not null.
	 * 
	 * @param       string|null     $message        source message
	 * @return      HtmlInputProcessor
	 */
	public function getHtmlInputProcessor($message = null);
	
	/**
	 * Returns a message list object.
	 * 
	 * @param	DatabaseObject		$container
	 * @param	integer			$lastMessageTime
	 * @return	DatabaseObjectList
	 */
	public function getMessageList(DatabaseObject $container, $lastMessageTime);
	
	/**
	 * Returns page no for given container object.
	 * 
	 * @param	DatabaseObject		$container
	 * @return	array
	 */
	public function getPageNo(DatabaseObject $container);
	
	/**
	 * Returns the redirect url.
	 * 
	 * @param	DatabaseObject		$container
	 * @param	DatabaseObject		$message
	 * @return	string
	 */
	public function getRedirectUrl(DatabaseObject $container, DatabaseObject $message);
	
	/**
	 * Validates the message.
	 * 
	 * @param	DatabaseObject		$container
	 * @param	HtmlInputProcessor      $htmlInputProcessor
	 */
	public function validateMessage(DatabaseObject $container, HtmlInputProcessor $htmlInputProcessor);
	
	/**
	 * Creates a new message and returns it.
	 * 
	 * @return	array
	 */
	public function quickReply();
	
	/**
	 * Validates the container object for quick reply.
	 * 
	 * @param	DatabaseObject		$container
	 */
	public function validateContainer(DatabaseObject $container);
	
	/**
	 * Validates parameters for quick reply.
	 */
	public function validateQuickReply();
}
