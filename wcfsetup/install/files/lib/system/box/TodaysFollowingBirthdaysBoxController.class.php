<?php
namespace wcf\system\box;
use wcf\system\WCF;

/**
 * Shows today's birthdays of users the active user is following.
 *
 * @author	Marcel Werk
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
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
		/** @noinspection PhpUndefinedMethodInspection */
		$userIDs = array_intersect($userIDs, WCF::getUserProfileHandler()->getFollowingUsers());
	}
}
