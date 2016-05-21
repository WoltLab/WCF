<?php
namespace wcf\acp\page;
use wcf\page\MultipleLinkPage;

/**
 * Lists the available captcha questions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class CaptchaQuestionListPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.captcha.question.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.captcha.canManageCaptchaQuestion'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = 'wcf\data\captcha\question\CaptchaQuestionList';
	
	/**
	 * @inheritDoc
	 */
	public $sortField = 'questionID';
	
	/**
	 * @inheritDoc
	 */
	public $sortOrder = 'ASC';
}
