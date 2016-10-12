<?php
namespace wcf\data\captcha\question;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;

/**
 * Executes captcha question-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Captcha\Question
 * 
 * @method	CaptchaQuestion			create()
 * @method	CaptchaQuestionEditor[]		getObjects()
 * @method	CaptchaQuestionEditor		getSingleObject()
 */
class CaptchaQuestionAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.captcha.canManageCaptchaQuestion'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.captcha.canManageCaptchaQuestion'];
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->getObjects() as $question) {
			$question->update([
				'isDisabled' => $question->isDisabled ? 0 : 1
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		parent::validateUpdate();
	}
}
