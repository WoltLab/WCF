<?php
namespace wcf\acp\page;
use wcf\page\MultipleLinkPage;

/**
 * Lists the available captcha questions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class CaptchaQuestionListPage extends MultipleLinkPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.captcha.question.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.captcha.canManageCaptchaQuestion');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\captcha\question\CaptchaQuestionList';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$sortField
	 */
	public $sortField = 'questionID';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$sortOrder
	 */
	public $sortOrder = 'ASC';
}
