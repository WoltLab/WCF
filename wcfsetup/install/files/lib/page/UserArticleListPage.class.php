<?php

namespace wcf\page;

use wcf\data\user\User;
use wcf\http\Helper;
use wcf\system\listView\user\UserArticleListView;
use wcf\system\WCF;

/**
 * Shows a list of articles by a certain user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractListViewPage<UserArticleListView>
 */
class UserArticleListPage extends AbstractListViewPage
{
    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_ARTICLE'];

    public User $user;

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        $this->user = Helper::fetchObjectFromQueryParameter(User::class);
    }

    #[\Override]
    public function assignVariables(): void
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'user' => $this->user,
        ]);
    }

    #[\Override]
    protected function createListView(): UserArticleListView
    {
        return new UserArticleListView($this->user->userID);
    }

    #[\Override]
    protected function getBaseUrlParameters(): array
    {
        return [
            'object' => $this->user,
        ];
    }
}
