<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;

/**
 * Condition implementation if it is the active user's birthday today.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class UserBirthdayCondition extends AbstractCondition implements IContentCondition {
	/**
	 * 1 if birthday today checkbox is checked
	 * @var	integer
	 */
	protected $birthdayToday = 0;
	
	/**
	 * @see	\wcf\system\condition\ICondition::getData()
	 */
	public function getData() {
		if ($this->birthdayToday) {
			return array(
				'birthdayToday' => 1
			);
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::getHTML()
	 */
	public function getHTML() {
		$label = WCF::getLanguage()->get('wcf.user.birthdayToday');
		$checked = '';
		if ($this->birthdayToday) {
			$checked = ' checked="checked"';
		}
		
		return <<<HTML
<dl>
	<dt></dt>
	<dd>
		<label><input type="checkbox" name="birthdayToday" id="birthdayToday"{$checked} /> {$label}</label>
	</dd>
</dl>
HTML;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST['birthdayToday'])) $this->birthdayToday = 1;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::reset()
	 */
	public function reset() {
		$this->birthdayToday = 0;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function setData(Condition $condition) {
		$this->birthdayToday = $condition->birthdayToday;
	}
	
	/**
	 * @see	\wcf\system\condition\IContentCondition::showContent()
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		$dateTime = new \DateTime();
		$dateTime->setTimezone(WCF::getUser()->getTimeZone());
		
		$userIDs = UserBirthdayCache::getInstance()->getBirthdays($dateTime->format('n'), $dateTime->format('j'));
		
		return in_array(WCF::getUser()->userID, $userIDs);
	}
}
