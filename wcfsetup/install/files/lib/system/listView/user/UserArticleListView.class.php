<?php

namespace wcf\system\listView\user;

use wcf\data\article\AccessibleArticleList;

/**
 * List view for the list of articles created by a specific user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class UserArticleListView extends ArticleListView
{
    public function __construct(public readonly int $userID)
    {
        parent::__construct();
    }

    #[\Override]
    protected function createObjectList(): AccessibleArticleList
    {
        $list = parent::createObjectList();
        $list->getConditionBuilder()->add('article.userID = ?', [$this->userID]);

        return $list;
    }

    #[\Override]
    public function addAvailableFilters(array $filters): void
    {
        // Remove useless username filter.
        $filters = \array_filter(
            $filters,
            static fn($filter) => $filter->getId() !== 'username'
        );

        parent::addAvailableFilters($filters);
    }

    #[\Override]
    public function getParameters(): array
    {
        return [
            'userID' => $this->userID,
        ];
    }
}
