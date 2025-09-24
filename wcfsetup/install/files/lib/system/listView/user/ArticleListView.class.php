<?php

namespace wcf\system\listView\user;

use wcf\data\article\AccessibleArticleList;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\ViewableArticle;
use wcf\data\DatabaseObjectList;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\object\type\ObjectTypeCache;
use wcf\event\listView\user\ArticleListViewInitialized;
use wcf\system\interaction\bulk\user\ArticleBulkInteractions;
use wcf\system\interaction\user\ArticleInteractions;
use wcf\system\label\LabelHandler;
use wcf\system\listView\AbstractListView;
use wcf\system\view\filter\BooleanFilter;
use wcf\system\view\filter\LabelFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\listView\ListViewSortField;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * List view for the list of articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractListView<ViewableArticle, AccessibleArticleList>
 */
class ArticleListView extends AbstractListView
{
    public function __construct()
    {
        $this->addAvailableSortFields([
            new ListViewSortField('time', 'wcf.global.date'),
            new ListViewSortField('title', 'wcf.global.title', 'title'),
        ]);
        $this->addAvailableFilters([
            $this->getTitleFilter(),
            new TextFilter('username', 'wcf.user.username'),
            ...$this->getLabelFilters()
        ]);
        if (WCF::getUser()->userID) {
            $this->addAvailableFilter($this->getUnreadFilter());
            if (ArticleCategory::getSubscribedCategoryIDs() !== []) {
                $this->addAvailableFilter($this->getWatchedFilter());
            }
        }

        $this->setInteractionProvider(new ArticleInteractions());
        $this->setBulkInteractionProvider(new ArticleBulkInteractions());
        $this->setItemsPerPage(\ARTICLES_PER_PAGE);
        $this->setDefaultSortField('time');
        $this->setDefaultSortOrder(\ARTICLE_SORT_ORDER);
        $this->setCssClassName('entryCardList');
        $this->setContainerCssClassName('entryCardList__container');
    }

    #[\Override]
    protected function createObjectList(): AccessibleArticleList
    {
        $list = new AccessibleArticleList();
        if ($list->sqlSelects !== '') {
            $list->sqlSelects .= ',';
        }
        $list->sqlSelects .= "(
            SELECT  title
            FROM    wcf1_article_content
            WHERE   articleID = article.articleID
                AND (
                        languageID IS NULL
                     OR languageID = " . WCF::getLanguage()->languageID . "
                    )
            LIMIT   1
        ) AS title";

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return !!\MODULE_ARTICLE;
    }

    #[\Override]
    public function renderItems(): string
    {
        return WCF::getTPL()->render('wcf', 'articleListItems', ['view' => $this]);
    }

    /**
     * @return ViewableLabelGroup[]
     */
    protected function getAccessibleLabelGroups(): array
    {
        return ArticleCategory::getAccessibleLabelGroups('canViewLabel');
    }

    /**
     * @return LabelFilter[]
     */
    protected function getLabelFilters(): array
    {
        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
            'com.woltlab.wcf.label.object',
            'com.woltlab.wcf.article'
        );

        $labelFilters = [];
        foreach ($this->getAccessibleLabelGroups() as $groupID => $categoryIDs) {
            $labelFilters[] = new LabelFilter(
                LabelHandler::getInstance()->getLabelGroup($groupID),
                $objectTypeID,
                'labelIDs' . $groupID
            );
        }

        return $labelFilters;
    }

    protected function getTitleFilter(): TextFilter
    {
        return new class('title', 'wcf.global.title') extends TextFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $value): void
            {
                $list->getConditionBuilder()->add(
                    "article.articleID IN (
                            SELECT  articleID
                            FROM    wcf1_article_content
                            WHERE   title LIKE ?
                                AND (
                                        languageID IS NULL
                                     OR languageID = " . WCF::getLanguage()->languageID . "
                                    )
                            )",
                    ['%' . WCF::getDB()->escapeLikeValue($value) . '%']
                );
            }
        };
    }

    protected function getUnreadFilter(): BooleanFilter
    {
        return new class('unread', 'wcf.article.unreadArticles') extends BooleanFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $value): void
            {
                $list->getConditionBuilder()->add(
                    'article.time > ?',
                    [VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.article')]
                );

                $list->sqlConditionJoins = "
                    LEFT JOIN   wcf1_tracked_visit tracked_visit
                    ON          tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.woltlab.wcf.article') . "
                            AND tracked_visit.objectID = article.articleID
                            AND tracked_visit.userID = " . WCF::getUser()->userID;
                $list->getConditionBuilder()->add("(article.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)");
            }
        };
    }

    protected function getWatchedFilter(): BooleanFilter
    {
        return new class('watched', 'wcf.article.watchedArticles') extends BooleanFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $value): void
            {
                $list->getConditionBuilder()->add(
                    'article.categoryID IN (?)',
                    [ArticleCategory::getSubscribedCategoryIDs()]
                );
            }
        };
    }

    #[\Override]
    protected function getInitializedEvent(): ArticleListViewInitialized
    {
        return new ArticleListViewInitialized($this);
    }
}
