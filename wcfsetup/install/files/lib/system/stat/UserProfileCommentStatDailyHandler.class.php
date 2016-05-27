<?php
namespace wcf\system\stat;

/**
 * Stat handler implementation for user profile comments.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.stat
 * @category	Community Framework
 */
class UserProfileCommentStatDailyHandler extends AbstractCommentStatDailyHandler {
	/**
	 * @inheritDoc
	 */
	protected $objectType = 'com.woltlab.wcf.user.profileComment';
}
