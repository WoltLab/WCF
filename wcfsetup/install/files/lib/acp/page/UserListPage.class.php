<?php

namespace wcf\acp\page;

use wcf\data\user\group\UserGroup;
use wcf\data\user\option\ViewableUserOption;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\page\SortablePage;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\option\IOptionHandler;
use wcf\system\option\user\IUserOptionOutput;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Shows the result of a user search.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserListPage extends SortablePage
{
    /**
     * list of displayed column names
     * @var string[]
     */
    public $columnHeads = [];

    /**
     * list of selected columns
     * @var string[]
     */
    public $columns = ['registrationDate', 'lastActivityTime'];

    /**
     * applies special CSS classes for selected columns
     * @var array
     */
    public $columnStyling = [
        'registrationDate' => 'columnDate',
        'lastActivityTime' => 'columnDate',
        'profileHits' => 'columnDigits',
        'activityPoints' => 'columnDigits',
        'likesReceived' => 'columnDigits',
    ];

    /**
     * list of column values
     * @var string[][]
     */
    public $columnValues = [];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'username';

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 50;

    /**
     * list of marked user ids
     * @var int[]
     */
    public $markedUsers = [];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canSearchUser'];

    /**
     * IOptionHandler object
     * @var IOptionHandler
     */
    protected $optionHandler;

    /**
     * list of available user option names
     * @var array
     */
    public $options = [];

    /**
     * id of a user search
     * @var int
     */
    public $searchID = 0;

    /**
     * list of user ids
     * @var int[]
     */
    public $userIDs = [];

    /**
     * list of users
     * @var UserProfile[]
     */
    public $users = [];

    /**
     * page url
     * @var string
     */
    public $url = '';

    /**
     * condition builder for user filtering
     * @var PreparedStatementConditionBuilder
     */
    public $conditions;

    /**
     * @inheritDoc
     */
    public $validSortFields = [
        'userID',
        'registrationDate',
        'username',
        'lastActivityTime',
        'profileHits',
        'activityPoints',
        'likesReceived',
    ];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $this->conditions = new PreparedStatementConditionBuilder();

        if (!empty($_REQUEST['id'])) {
            $this->searchID = \intval($_REQUEST['id']);
            if ($this->searchID) {
                $this->readSearchResult();
            }
            if (empty($this->userIDs)) {
                throw new IllegalLinkException();
            }
            $this->conditions->add("user_table.userID IN (?)", [$this->userIDs]);
        }

        // get user options
        $this->readUserOptions();
    }

    /**
     * @inheritDoc
     */
    public function validateSortField()
    {
        // add options to valid sort fields
        $this->validSortFields = \array_merge($this->validSortFields, \array_keys($this->options));

        // avoid leaking mail adresses by sorting
        if (WCF::getSession()->getPermission('admin.user.canEditMailAddress')) {
            $this->validSortFields[] = 'email';
        }

        parent::validateSortField();
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // add email column for authorized users
        if (!$this->searchID && WCF::getSession()->getPermission('admin.user.canEditMailAddress')) {
            \array_unshift($this->columns, 'email');
        }

        // get marked users
        $this->markedUsers = WCF::getSession()->getVar('markedUsers');
        if ($this->markedUsers == null || !\is_array($this->markedUsers)) {
            $this->markedUsers = [];
        }

        // get columns heads
        $this->readColumnsHeads();

        // get users
        $this->readUsers();

        // build page url
        $this->url = LinkHandler::getInstance()->getLink(
            'UserList',
            [],
            'searchID=' . $this->searchID . '&action=' . \rawurlencode($this->action) . '&pageNo=' . $this->pageNo . '&sortField=' . $this->sortField . '&sortOrder=' . $this->sortOrder
        );
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'users' => $this->users,
            'searchID' => $this->searchID,
            'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user')),
            'url' => $this->url,
            'columnHeads' => $this->columnHeads,
            'columnValues' => $this->columnValues,
            'columnStyling' => $this->columnStyling,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        $this->activeMenuItem = 'wcf.acp.menu.link.user.' . ($this->searchID ? 'search' : 'list');

        parent::show();
    }

    /**
     * @inheritDoc
     */
    public function countItems()
    {
        // call countItems event
        EventHandler::getInstance()->fireAction($this, 'countItems');

        $sql = "SELECT  COUNT(*)
                FROM    wcf" . WCF_N . "_user user_table
                " . $this->conditions;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($this->conditions->getParameters());

        return $statement->fetchSingleColumn();
    }

    /**
     * Fetches the list of results.
     */
    protected function readUsers()
    {
        // get user ids
        $sql = "SELECT      user_table.userID
                FROM        wcf" . WCF_N . "_user user_table
                " . (isset($this->options[$this->sortField]) ? "
                    LEFT JOIN   wcf" . WCF_N . "_user_option_value user_option_value
                    ON          user_option_value.userID = user_table.userID
                    " : '') . "
                " . $this->conditions . "
                ORDER BY    " . (($this->sortField != 'email' && isset($this->options[$this->sortField])) ? 'user_option_value.userOption' . $this->options[$this->sortField]->optionID : $this->sortField) . " " . $this->sortOrder;
        $statement = WCF::getDB()->prepareStatement(
            $sql,
            $this->itemsPerPage,
            ($this->pageNo - 1) * $this->itemsPerPage
        );
        $statement->execute($this->conditions->getParameters());
        $userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        // get user data
        if (!empty($userIDs)) {
            // get group ids
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("user_table.userID IN (?)", [$userIDs]);

            $sql = "SELECT  userID, groupID
                    FROM    wcf" . WCF_N . "_user_to_group user_table
                    " . $conditions;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditions->getParameters());
            $userToGroups = $statement->fetchMap('userID', 'groupID', false);

            $sql = "SELECT      user_avatar.*, option_value.*, user_table.*
                    FROM        wcf" . WCF_N . "_user user_table
                    LEFT JOIN   wcf" . WCF_N . "_user_option_value option_value
                    ON          option_value.userID = user_table.userID
                    LEFT JOIN   wcf" . WCF_N . "_user_avatar user_avatar
                    ON          user_avatar.avatarID = user_table.avatarID
                    " . $conditions . "
                    ORDER BY    " . (($this->sortField != 'email' && isset($this->options[$this->sortField])) ? 'option_value.userOption' . $this->options[$this->sortField]->optionID : 'user_table.' . $this->sortField) . " " . $this->sortOrder;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditions->getParameters());
            while ($row = $statement->fetchArray()) {
                $groupIDs = ($userToGroups[$row['userID']] ?? []);

                $row['groupIDs'] = \implode(',', $groupIDs);
                $accessible = (!empty($groupIDs) ? UserGroup::isAccessibleGroup($groupIDs) : true);
                $row['accessible'] = $accessible;
                $row['deletable'] = ($accessible && WCF::getSession()->getPermission('admin.user.canDeleteUser') && $row['userID'] != WCF::getUser()->userID) ? 1 : 0;
                $row['editable'] = ($accessible && WCF::getSession()->getPermission('admin.user.canEditUser')) ? 1 : 0;
                $row['bannable'] = ($accessible && WCF::getSession()->getPermission('admin.user.canBanUser') && $row['userID'] != WCF::getUser()->userID) ? 1 : 0;
                $row['canBeEnabled'] = ($accessible && WCF::getSession()->getPermission('admin.user.canEnableUser') && $row['userID'] != WCF::getUser()->userID) ? 1 : 0;
                $row['isMarked'] = \intval(\in_array($row['userID'], $this->markedUsers));

                $this->users[] = new UserProfile(new User(null, $row));
            }

            // get special columns
            foreach ($this->users as $user) {
                foreach ($this->columns as $column) {
                    switch ($column) {
                        case 'email':
                            $this->columnValues[$user->userID][$column] = '<a href="mailto:' . $user->getEncodedEmail() . '">' . StringUtil::encodeHTML($user->email) . '</a>';
                            break;

                        case 'registrationDate':
                            $this->columnValues[$user->userID][$column] = DateUtil::format(
                                DateUtil::getDateTimeByTimestamp($user->{$column}),
                                DateUtil::DATE_FORMAT
                            );
                            break;

                        case 'lastActivityTime':
                            if ($user->{$column}) {
                                $this->columnValues[$user->userID][$column] = \str_replace(
                                    '%time%',
                                    DateUtil::format(
                                        DateUtil::getDateTimeByTimestamp($user->{$column}),
                                        DateUtil::TIME_FORMAT
                                    ),
                                    \str_replace(
                                        '%date%',
                                        DateUtil::format(
                                            DateUtil::getDateTimeByTimestamp($user->{$column}),
                                            DateUtil::DATE_FORMAT
                                        ),
                                        WCF::getLanguage()->get('wcf.date.dateTimeFormat')
                                    )
                                );
                            }
                            break;

                        case 'profileHits':
                        case 'activityPoints':
                        case 'likesReceived':
                            $this->columnValues[$user->userID][$column] = StringUtil::formatNumeric($user->{$column});
                            break;

                        default:
                            if (isset($this->options[$column])) {
                                if ($this->options[$column]->outputClass) {
                                    $this->options[$column]->setOptionValue($user->getDecoratedObject());

                                    /** @var IUserOptionOutput $outputObj */
                                    $outputObj = $this->options[$column]->getOutputObject();
                                    $this->columnValues[$user->userID][$column] = $outputObj->getOutput(
                                        $user->getDecoratedObject(),
                                        $this->options[$column]->getDecoratedObject(),
                                        $user->{$column}
                                    );
                                } else {
                                    $this->columnValues[$user->userID][$column] = StringUtil::encodeHTML($user->{$column});
                                }
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * Fetches the result of the search with the given search id.
     */
    protected function readSearchResult()
    {
        // get user search from database
        $sql = "SELECT  searchData
                FROM    wcf" . WCF_N . "_search
                WHERE   searchID = ?
                    AND userID = ?
                    AND searchType = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            $this->searchID,
            WCF::getUser()->userID,
            'users',
        ]);
        $search = $statement->fetchArray();
        if (!isset($search['searchData'])) {
            throw new IllegalLinkException();
        }

        $data = \unserialize($search['searchData']);
        $this->userIDs = $data['matches'];
        $this->itemsPerPage = $data['itemsPerPage'];
        $this->columns = $data['columns'];
        unset($data);
    }

    /**
     * Fetches the user options from cache.
     */
    protected function readUserOptions()
    {
        $this->options = UserOptionCacheBuilder::getInstance()->getData([], 'options');

        foreach ($this->options as &$option) {
            $option = new ViewableUserOption($option);
        }
        unset($option);
    }

    /**
     * Reads the column heads.
     */
    protected function readColumnsHeads()
    {
        foreach ($this->columns as $column) {
            if ($column == 'likesReceived') {
                $this->columnHeads[$column] = 'wcf.like.likesReceived';
                continue;
            }
            if ($column == 'activityPoints') {
                $this->columnHeads[$column] = 'wcf.user.activityPoint';
                continue;
            }

            if (isset($this->options[$column]) && $column != 'email') {
                $this->columnHeads[$column] = 'wcf.user.option.' . $column;
            } else {
                $this->columnHeads[$column] = 'wcf.user.' . $column;
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    protected function readObjects()
    {
        // does nothing
    }
}
