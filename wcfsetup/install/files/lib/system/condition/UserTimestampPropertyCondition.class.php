<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\system\WCF;

/**
 * Condition implementation for comparing a user-bound timestamp with a fixed time
 * interval.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserTimestampPropertyCondition extends AbstractTimestampCondition implements IContentCondition, IUserCondition
{
    use TObjectListUserCondition;
    use TObjectUserCondition;

    /**
     * @inheritDoc
     */
    protected $className = User::class;

    #[\Override]
    protected function getLanguageItemPrefix()
    {
        return 'wcf.user.condition';
    }

    #[\Override]
    protected function getPropertyName()
    {
        return $this->getDecoratedObject()->propertyname;
    }

    #[\Override]
    public function showContent(Condition $condition)
    {
        if (WCF::getUser()->isGuest()) {
            return false;
        }

        return $this->checkUser($condition, WCF::getUser());
    }
}
