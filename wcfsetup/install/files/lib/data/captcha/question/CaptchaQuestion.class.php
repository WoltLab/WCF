<?php
namespace wcf\data\captcha\question;
use wcf\data\DatabaseObject;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a captcha question.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.captcha.question
 * @category	Community Framework
 */
class CaptchaQuestion extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'captcha_question';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'questionID';
	
	/**
	 * Returns true if the given user input is an answer to this question.
	 * 
	 * @param	string		$answer
	 * @return	boolean
	 */
	public function isAnswer($answer) {
		$answers = explode("\n", StringUtil::unifyNewlines(WCF::getLanguage()->get($this->answers)));
		foreach ($answers as $__answer) {
			if (mb_substr($__answer, 0, 1) == '~' && mb_substr($__answer, -1, 1) == '~') {
				if (Regex::compile(mb_substr($__answer, 1, mb_strlen($__answer) - 2))->match($answer)) {
					return true;
				}
				
				continue;
			}
			else if ($__answer == $answer) {
				return true;
			}
		}
		
		return false;
	}
}
