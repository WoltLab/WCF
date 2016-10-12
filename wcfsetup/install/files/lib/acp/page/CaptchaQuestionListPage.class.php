<?php
namespace wcf\acp\page;
use wcf\data\captcha\question\CaptchaQuestionList;
use wcf\page\MultipleLinkPage;

/**
 * Lists the available captcha questions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	CaptchaQuestionList	$objectList
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
	public $objectListClassName = CaptchaQuestionList::class;
	
	/**
	 * @inheritDoc
	 */
	public $sortField = 'questionID';
	
	/**
	 * @inheritDoc
	 */
	public $sortOrder = 'ASC';
}
