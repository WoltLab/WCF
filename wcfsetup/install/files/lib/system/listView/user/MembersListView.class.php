<?php

namespace wcf\system\listView\user;

use wcf\data\DatabaseObjectList;
use wcf\data\search\Search;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;
use wcf\event\listView\user\MembersListViewInitialized;
use wcf\system\interaction\InteractionContextMenuComponentConfiguration;
use wcf\system\interaction\user\UserCardInteractions;
use wcf\system\interaction\user\UserCardQuickInteractions;
use wcf\system\listView\AbstractListView;
use wcf\system\listView\filter\exception\InvalidFilterValue;
use wcf\system\listView\filter\SelectFilter;
use wcf\system\listView\filter\TextFilter;
use wcf\system\listView\ListViewSortField;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * List view for the members list.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractListView<UserProfile, UserProfileList>
 */
class MembersListView extends AbstractListView
{
    private bool $userStoragePreloaded = false;
    private ?Search $search = null;

    public function __construct(private readonly ?int $searchID = null)
    {
        $this->addAvailableSortFields([
            new ListViewSortField('username', 'wcf.user.sortField.username'),
            new ListViewSortField('registrationDate', 'wcf.user.sortField.registrationDate'),
            new ListViewSortField('activityPoints', 'wcf.user.sortField.activityPoints'),
            new ListViewSortField('likesReceived', 'wcf.user.sortField.likesReceived'),
            new ListViewSortField('lastActivityTime', 'wcf.user.sortField.lastActivityTime'),
        ]);

        $this->addAvailableFilters([
            $this->getFirstLetterFilter(),
            new TextFilter('username', 'wcf.user.username'),
        ]);

        $this->setQuickInteractionProvider(new UserCardQuickInteractions());
        $this->setInteractionProvider(new UserCardInteractions());
        $this->setInteractionContextMenuComponentConfiguration(new InteractionContextMenuComponentConfiguration(
            'userCard__button',
            'ellipsis',
            24
        ));

        if (\in_array(\MEMBERS_LIST_DEFAULT_SORT_FIELD, \array_map(static fn($sortField) => $sortField->id, $this->getAvailableSortFields()))) {
            $this->setSortField(\MEMBERS_LIST_DEFAULT_SORT_FIELD);
            $this->setSortOrder(\MEMBERS_LIST_DEFAULT_SORT_ORDER);
        } else {
            $this->setSortField('username');
        }

        $this->setItemsPerPage(\MEMBERS_LIST_USERS_PER_PAGE);
        $this->setCssClassName('userCardList');
        $this->setContainerCssClassName('userCardList__container');

        if ($this->searchID !== null) {
            $this->search = new Search($this->searchID);
        }
    }

    #[\Override]
    protected function createObjectList(): UserProfileList
    {
        $list = new UserProfileList();

        if ($this->search !== null) {
            $searchData = \unserialize($this->search->searchData);
            $list->getConditionBuilder()->add("user_table.userID IN (?)", [$searchData['matches']]);
            unset($searchData);
        }

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        if (!\MODULE_MEMBERS_LIST || !WCF::getSession()->getPermission('user.profile.canViewMembersList')) {
            return false;
        }

        if ($this->search !== null) {
            if (!$this->search->searchID || $this->search->userID !== WCF::getUser()->userID || $this->search->searchType !== 'users') {
                return false;
            }
        }

        return true;
    }

    #[\Override]
    public function renderItems(): string
    {
        return WCF::getTPL()->render('wcf', 'membersListItems', ['view' => $this]);
    }

    #[\Override]
    public function getItems(): array
    {
        $objects = parent::getItems();

        // Preload user storage to avoid reading storage for each user separately at runtime.
        if ($objects !== [] && !$this->userStoragePreloaded) {
            $this->userStoragePreloaded = true;
            UserStorageHandler::getInstance()->loadStorage(\array_keys($objects));
        }

        return $objects;
    }

    #[\Override]
    public function getParameters(): array
    {
        if ($this->searchID !== null) {
            return [
                'searchID' => $this->searchID,
            ];
        }

        return [];
    }

    #[\Override]
    protected function getInitializedEvent(): MembersListViewInitialized
    {
        return new MembersListViewInitialized($this);
    }

    protected function getFirstLetterFilter(): SelectFilter
    {
        $availableLetters = \str_split('#ABCDEFGHIJKLMNOPQRSTUVWXYZ');

        return new class(
            \array_combine($availableLetters, $availableLetters),
            'firstLetter',
            'wcf.user.members.sort.letters',
            labelLanguageItems: false
        ) extends SelectFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $value): void
            {
                if (!isset($this->options[$value])) {
                    throw new InvalidFilterValue("Invalid value '{$value}' for filter '{$this->id}' given.");
                }

                if ($value == '#') {
                    $list->getConditionBuilder()->add("SUBSTRING(username,1,1) IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9')");
                } else {
                    $list->getConditionBuilder()->add("username LIKE ?", [$value . '%']);
                }
            }
        };
    }
}
