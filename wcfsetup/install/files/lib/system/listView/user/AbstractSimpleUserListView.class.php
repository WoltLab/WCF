<?php

namespace wcf\system\listView\user;

use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;
use wcf\system\interaction\user\UserProfileInteractionsWithFollow;
use wcf\system\listView\AbstractListView;
use wcf\system\listView\ListViewSortField;
use wcf\system\WCF;

/**
 * Abstract implementation of a list view for a simple user list.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractListView<UserProfile, UserProfileList>
 */
abstract class AbstractSimpleUserListView extends AbstractListView
{
    public function __construct()
    {
        $this->addAvailableSortFields([
            new ListViewSortField(
                'username',
                'wcf.user.username',
            ),
        ]);

        $this->setAllowSorting(false);
        $this->setInteractionProvider(new UserProfileInteractionsWithFollow());
        $this->setDefaultSortField('username');
        $this->setItemsPerPage(100);
        $this->setCssClassName('simpleUserList');
        $this->setContainerCssClassName('simpleUserList__container');
    }

    #[\Override]
    public function renderItems(): string
    {
        return WCF::getTPL()->render('wcf', 'shared_simpleUserListItems', ['view' => $this]);
    }
}
