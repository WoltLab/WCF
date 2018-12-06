<?php
namespace wcf\data\captcha\question;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;

/**
 * Executes captcha question-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Captcha\Question
 * 
 * @method	CaptchaQuestion			create()
 * @method	CaptchaQuestionEditor[]		getObjects()
 * @method	CaptchaQuestionEditor		getSingleObject()
 */
class CaptchaQuestionAction extends AbstractDatabaseObjectAction implements IToggleAction {
	use TDatabaseObjectToggle;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.captcha.canManageCaptchaQuestion'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.captcha.canManageCaptchaQuestion'];
}
