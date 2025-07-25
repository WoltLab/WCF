<?php

namespace wcf\page;

use wcf\data\search\Search;
use wcf\system\exception\IllegalLinkException;
use wcf\system\listView\user\MembersListView;
use wcf\system\listView\user\UserSearchResultsListView;
use wcf\system\WCF;

/**
 * Shows members page.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractListViewPage<MembersListView>
 */
class MembersListPage extends AbstractListViewPage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.profile.canViewMembersList'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_MEMBERS_LIST'];

    public ?int $searchID = null;

    /**
     * @deprecated 6.2 No longer in use, but here for backwards compatibility.
     */
    public $validSortFields = ['username', 'registrationDate', 'activityPoints', 'likesReceived', 'lastActivityTime'];

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['id'])) {
            $this->searchID = \intval($_REQUEST['id']);
            $search = new Search($this->searchID);
            if (!$search->searchID || $search->userID != WCF::getUser()->userID || $search->searchType != 'users') {
                throw new IllegalLinkException();
            }
        }
    }

    #[\Override]
    protected function createListView(): MembersListView
    {
        if ($this->searchID) {
            return new UserSearchResultsListView($this->searchID);
        }

        return new MembersListView();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'searchID' => $this->searchID,
        ]);
    }

    #[\Override]
    protected function getBaseUrlParameters(): array
    {
        if ($this->searchID) {
            return [
                'id' => $this->searchID,
            ];
        }

        return parent::getBaseUrlParameters();
    }
}
