<?php
namespace wcf\system\box;
use wcf\system\WCF;

/**
 * Shows today's birthdays of users the active user is following.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 * @since	2.2
 */
class TodaysFollowingBirthdaysBoxController extends TodaysBirthdaysBoxController {
	/**
	 * @inheritDoc
	 */
	protected $templateName = 'boxTodaysFollowingBirthdays';
	
	/**
	 * @inheritDoc
	 */
	protected function filterUserIDs(&$userIDs) {
		$userIDs = array_intersect($userIDs, WCF::getUserProfileHandler()->getFollowingUsers());
	}
}
