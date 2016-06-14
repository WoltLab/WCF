<?php
namespace wcf\data\captcha\question;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\CaptchaQuestionCacheBuilder;

/**
 * Provides functions to edit captcha questions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Captcha\Question
 * 
 * @method	CaptchaQuestion		getDecoratedObject()
 * @mixin	CaptchaQuestion
 */
class CaptchaQuestionEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CaptchaQuestion::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		CaptchaQuestionCacheBuilder::getInstance()->reset();
	}
}
