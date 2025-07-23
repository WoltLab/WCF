<?php

namespace wcf\system\listView\user;

use wcf\data\search\Search;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;
use wcf\system\listView\AbstractListView;
use wcf\system\WCF;

/**
 * List view for results of the user search.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class UserSearchResultsListView extends MembersListView
{
    private Search $search;

    public function __construct(private readonly int $searchID)
    {
        parent::__construct();

        $this->search = new Search($this->searchID);
    }

    #[\Override]
    protected function createObjectList(): UserProfileList
    {
        $list = parent::createObjectList();

        $searchData = \unserialize($this->search->searchData);
        $list->getConditionBuilder()->add("user_table.userID IN (?)", [$searchData['matches']]);

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        if (!parent::isAccessible()) {
            return false;
        }

        if (!$this->search->searchID || $this->search->userID !== WCF::getUser()->userID || $this->search->searchType !== 'users') {
            return false;
        }

        return true;
    }

    #[\Override]
    public function getParameters(): array
    {
        return [
            'searchID' => $this->searchID,
        ];
    }
}
