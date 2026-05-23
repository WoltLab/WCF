<?php

namespace wcf\system\listView\user;

use wcf\data\user\option\UserOption;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;
use wcf\event\listView\user\UserBirthdayListViewInitialized;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * List view showing the users whose birthdays fall on a specific date.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class UserBirthdayListView extends AbstractSimpleUserListView
{
    public function __construct(
        public readonly string $date
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function createObjectList(): UserProfileList
    {
        ['day' => $day, 'month' => $month, 'year' => $year] = $this->getDate();

        $userIDs = [];
        $userOptions = UserOptionCacheBuilder::getInstance()->getData([], 'options');
        if (isset($userOptions['birthday'])) {
            $birthdayUserOption = $userOptions['birthday'];
            \assert($birthdayUserOption instanceof UserOption);

            $userProfiles = UserProfileRuntimeCache::getInstance()->getObjects(
                $this->getBirthdays($month, $day)
            );

            foreach ($userProfiles as $user) {
                $birthdayUserOption->setUser($user->getDecoratedObject());

                if (!$user->isProtected() && $birthdayUserOption->isVisible() && $user->getAge($year) >= 0) {
                    $userIDs[] = $user->userID;
                }
            }
        }

        $list = new UserProfileList();
        if ($userIDs !== []) {
            $list->setObjectIDs($userIDs);
        } else {
            $list->getConditionBuilder()->add('1=0');
        }

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        if (!\MODULE_MEMBERS_LIST || !WCF::getSession()->hasPermission('user.profile.canViewMembersList')) {
            return false;
        }

        if (!\preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->date)) {
            return false;
        }

        return true;
    }

    #[\Override]
    protected function getInitializedEvent(): UserBirthdayListViewInitialized
    {
        return new UserBirthdayListViewInitialized($this);
    }

    #[\Override]
    public function getItemDescription(UserProfile $user): string
    {
        return StringUtil::encodeHTML(
            $user->getBirthday($this->getDate()['year'])
        );
    }

    #[\Override]
    public function getParameters(): array
    {
        return [
            'date' => $this->date,
        ];
    }

    /**
     * @return array{
     *  day: int,
     *  month: int,
     *  year: int
     * }
     */
    private function getDate(): array
    {
        $value = \explode('-', $this->date);
        if (\count($value) !== 3) {
            throw new \LogicException('unreachable');
        }

        return [
            'day' => \intval($value[2]),
            'month' => \intval($value[1]),
            'year' => \intval($value[0]),
        ];
    }

    protected function getBirthdays(int $month, int $day): array
    {
        return UserBirthdayCache::getInstance()->getBirthdays($month, $day);
    }
}
