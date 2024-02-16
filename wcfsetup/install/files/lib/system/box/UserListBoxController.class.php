<?php

namespace wcf\system\box;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObject;
use wcf\data\user\UserProfileList;
use wcf\system\cache\builder\MostActiveMembersCacheBuilder;
use wcf\system\cache\builder\MostLikedMembersCacheBuilder;
use wcf\system\cache\builder\NewestMembersCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\condition\IObjectListCondition;
use wcf\system\event\EventHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Box controller for a list of users.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class UserListBoxController extends AbstractDatabaseObjectListBoxController
{
    /**
     * maps special sort fields to cache builders
     * @var string[]
     */
    public $cacheBuilders = [
        'activityPoints' => MostActiveMembersCacheBuilder::class,
        'likesReceived' => MostLikedMembersCacheBuilder::class,
        'registrationDate' => NewestMembersCacheBuilder::class,
    ];

    /**
     * @inheritDoc
     */
    protected $conditionDefinition = 'com.woltlab.wcf.box.userList.condition';

    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];

    /**
     * @inheritDoc
     */
    public $defaultLimit = 5;

    /**
     * @inheritDoc
     */
    protected $sortFieldLanguageItemPrefix = 'wcf.user.sortField';

    /**
     * ids of the shown users loaded from cache
     * @var int[]|null
     */
    public $userIDs;

    /**
     * @inheritDoc
     */
    public $validSortFields = [
        'username',
        'activityPoints',
        'registrationDate',
    ];

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        if (!empty($this->validSortFields) && MODULE_LIKE) {
            $this->validSortFields[] = 'likesReceived';
        }

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        if (MODULE_MEMBERS_LIST) {
            $parameters = '';
            if ($this->sortField) {
                $parameters = 'sortField=' . $this->sortField . '&sortOrder=' . $this->sortOrder;
            }

            return LinkHandler::getInstance()->getLink('MembersList', [], $parameters);
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    protected function getObjectList()
    {
        // use specialized cache builders
        if ($this->sortOrder && $this->sortField && isset($this->cacheBuilders[$this->sortField])) {
            $conditions = \array_filter($this->box->getConditions(), static function (Condition $condition) {
                return $condition->getObjectType()->getProcessor() instanceof IObjectListCondition;
            });

            $this->userIDs = \call_user_func([$this->cacheBuilders[$this->sortField], 'getInstance'])->getData([
                'conditions' => $conditions,
                'limit' => $this->limit,
                'sortOrder' => $this->sortOrder,
            ]);
        }

        if ($this->userIDs !== null) {
            UserProfileRuntimeCache::getInstance()->cacheObjectIDs($this->userIDs);
        }

        return new UserProfileList();
    }

    /**
     * @inheritDoc
     */
    protected function getTemplate()
    {
        $userProfiles = [];
        if ($this->userIDs !== null) {
            $userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($this->userIDs);

            // filter `null` values of users that have been deleted in the meantime
            $userProfiles = \array_filter($userProfiles, static function ($userProfile) {
                return $userProfile !== null;
            });

            DatabaseObject::sort($userProfiles, $this->sortField, $this->sortOrder);
        }

        return WCF::getTPL()->fetch('boxUserList', 'wcf', [
            'boxUsers' => $this->userIDs !== null ? $userProfiles : $this->objectList->getObjects(),
            'boxSortField' => $this->box->sortField,
        ], true);
    }

    /**
     * @inheritDoc
     */
    public function hasContent()
    {
        $hasContent = parent::hasContent();

        if ($this->userIDs !== null) {
            return !empty($this->userIDs);
        }

        return $hasContent;
    }

    /**
     * @inheritDoc
     */
    public function hasLink()
    {
        return MODULE_MEMBERS_LIST == 1;
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        if ($this->userIDs === null) {
            parent::readObjects();
        } else {
            EventHandler::getInstance()->fireAction($this, 'readObjects');
        }
    }
}
