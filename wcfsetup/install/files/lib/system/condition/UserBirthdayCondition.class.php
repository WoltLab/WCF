<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;

/**
 * Condition implementation if it is the active user's birthday today.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserBirthdayCondition extends AbstractCondition implements IContentCondition
{
    /**
     * 1 if birthday today checkbox is checked
     * @var int
     */
    protected $birthdayToday = 0;

    #[\Override]
    public function getData()
    {
        if ($this->birthdayToday) {
            return ['birthdayToday' => 1];
        }

        return null;
    }

    #[\Override]
    public function getHTML()
    {
        $label = WCF::getLanguage()->get('wcf.user.birthdayToday');
        $checked = '';
        if ($this->birthdayToday) {
            $checked = ' checked';
        }

        return <<<HTML
<dl>
	<dt></dt>
	<dd>
		<label><input type="checkbox" name="birthdayToday" id="birthdayToday"{$checked}> {$label}</label>
	</dd>
</dl>
HTML;
    }

    #[\Override]
    public function readFormParameters()
    {
        if (isset($_POST['birthdayToday'])) {
            $this->birthdayToday = 1;
        }
    }

    #[\Override]
    public function reset()
    {
        $this->birthdayToday = 0;
    }

    #[\Override]
    public function setData(Condition $condition)
    {
        $this->birthdayToday = $condition->birthdayToday;
    }

    #[\Override]
    public function showContent(Condition $condition)
    {
        if (!WCF::getUser()->userID) {
            return false;
        }

        $dateTime = new \DateTimeImmutable('now', WCF::getUser()->getTimeZone());

        $userIDs = UserBirthdayCache::getInstance()->getBirthdays((int)$dateTime->format('n'), (int)$dateTime->format('j'));

        return \in_array(WCF::getUser()->userID, $userIDs);
    }
}
