<?php
namespace wcf\system\cache\builder;
use wcf\data\captcha\question\CaptchaQuestionList;

/**
 * Caches the enabled captcha questions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class CaptchaQuestionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$questionList = new CaptchaQuestionList();
		$questionList->getConditionBuilder()->add('isDisabled = ?', [0]);
		$questionList->readObjects();
		
		return $questionList->getObjects();
	}
}
