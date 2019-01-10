<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\comment\Comment;
use wcf\system\WCF;

/**
 * Implements IMultiRecipientCommentUserNotificationObjectType::getRecipientIDs()
 * for page comment user notification object types.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 * @since	5.2
 */
trait TMultiRecipientPageCommentUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	public function getRecipientIDs(Comment $comment) {
		// find all userIDs with the permission to manage pages
		$sql = "SELECT  userID 
			FROM    wcf".WCF_N."_user_to_group
                        INNER JOIN      wcf".WCF_N."_user_group_option_value
                                        ON      wcf".WCF_N."_user_to_group.groupID = wcf".WCF_N."_user_group_option_value.groupID  
                                        AND     wcf".WCF_N."_user_group_option_value.optionValue = ? 
                        INNER JOIN      wcf".WCF_N."_user_group_option
                                        ON      wcf".WCF_N."_user_group_option_value.optionID = wcf".WCF_N."_user_group_option.optionID 
                                        AND     wcf".WCF_N."_user_group_option.optionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			1, 
			'admin.content.cms.canManagePage'
		]);
		return $statement->fetchAll(\PDO::FETCH_COLUMN);
	}
}
