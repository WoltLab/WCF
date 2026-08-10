<?php

namespace wcf\system\box;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\user\option\UserOption;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\condition\IObjectCondition;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;

/**
 * Shows today's birthdays.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectListBoxController<DatabaseObjectList<DatabaseObject>>
 */
class TodaysBirthdaysBoxController extends AbstractDatabaseObjectListBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];

    /**
     * @inheritDoc
     * @since       5.3
     */
    protected $conditionDefinition = 'com.woltlab.wcf.box.todaysBirthdays.condition';

    /**
     * template name
     * @var string
     */
    protected $templateName = 'boxTodaysBirthdays';

    /**
     * @inheritDoc
     */
    public $defaultLimit = 5;

    /**
     * @inheritDoc
     */
    protected $sortFieldLanguageItemPrefix = 'wcf.user.sortField';

    /**
     * @inheritDoc
     */
    public $validSortFields = [
        'username',
        'activityPoints',
        'registrationDate',
    ];

    #[\Override]
    protected function getObjectList()
    {
        return null;
    }

    #[\Override]
    protected function getTemplate()
    {
        return '';
    }

    #[\Override]
    public function hasContent()
    {
        parent::hasContent();

        return AbstractBoxController::hasContent();
    }

    #[\Override]
    protected function loadContent()
    {
        // get current date
        $now = new \DateTimeImmutable("now", WCF::getUser()->getTimeZone());
        $currentDay = $now->format('m-d');
        $date = \explode('-', $now->format('Y-n-j'));

        // get user ids
        $userIDs = UserBirthdayCache::getInstance()->getBirthdays((int)$date[1], (int)$date[2]);
        $this->filterUserIDs($userIDs);

        if ($userIDs !== []) {
            $userOptions = UserOptionCacheBuilder::getInstance()->getData([], 'options');
            if (isset($userOptions['birthday'])) {
                /** @var UserOption $birthdayUserOption */
                $birthdayUserOption = $userOptions['birthday'];

                $userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
                $visibleUserProfiles = [];

                $i = 0;
                foreach ($userProfiles as $userProfile) {
                    // ignore deleted users
                    if ($userProfile === null) {
                        continue;
                    }

                    // show a maximum of x users
                    if ($i === $this->limit) {
                        break;
                    }

                    foreach ($this->box->getConditions() as $condition) {
                        /** @var IObjectCondition $processor */
                        $processor = $condition->getObjectType()->getProcessor();
                        if (!$processor->checkObject($userProfile->getDecoratedObject(), $condition->conditionData)) {
                            continue 2;
                        }
                    }

                    $birthdayUserOption->setUser($userProfile->getDecoratedObject());

                    if (
                        !$userProfile->isProtected() && $birthdayUserOption->isVisible() && \substr(
                            $userProfile->birthday,
                            5
                        ) === $currentDay
                    ) {
                        $visibleUserProfiles[] = $userProfile;
                        $i++;
                    }
                }

                if ($visibleUserProfiles !== []) {
                    // sort users
                    DatabaseObject::sort($visibleUserProfiles, $this->sortField, $this->sortOrder);

                    $this->content = WCF::getTPL()->render('wcf', $this->templateName, [
                        'birthdayUserProfiles' => $visibleUserProfiles,
                        'sortField' => $this->sortField,
                        'sortOrder' => $this->sortOrder,
                    ]);
                }
            }
        }
    }

    /**
     * Filters given user ids.
     *
     * @param int[] $userIDs
     * @return void
     */
    protected function filterUserIDs(&$userIDs)
    {
        // does nothing, can be overwritten by child classes
    }
}
