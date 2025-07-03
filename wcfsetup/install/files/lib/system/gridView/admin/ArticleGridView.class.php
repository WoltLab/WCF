<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\ArticleEditForm;
use wcf\acp\form\UserEditForm;
use wcf\data\article\AccessibleArticleList;
use wcf\data\article\Article;
use wcf\data\article\ViewableArticle;
use wcf\data\category\CategoryNodeTree;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\event\gridView\admin\ArticleGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\BooleanFilter;
use wcf\system\gridView\filter\CategoryFilter;
use wcf\system\gridView\filter\SelectFilter;
use wcf\system\gridView\filter\TextFilter;
use wcf\system\gridView\filter\TimeFilter;
use wcf\system\gridView\filter\UserFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\CategoryColumnRenderer;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\UserLinkColumnRenderer;
use wcf\system\interaction\admin\ArticleInteractions;
use wcf\system\interaction\bulk\admin\ArticleBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of articles.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<Article, AccessibleArticleList>
 */
final class ArticleGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('articleID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('teaserImage')
                ->label('wcf.acp.article.teaserImage')
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof ViewableArticle);

                            if ($row->getTeaserImage() !== null) {
                                return $row->getTeaserImage()->getElementTag(48);
                            }

                            return \sprintf(
                                '<img src="%simages/placeholderTiny.png" width="48" height="48" loading="lazy" alt="">',
                                WCF::getPath()
                            );
                        }

                        #[\Override]
                        public function getClasses(): string
                        {
                            return 'gridView__column--image';
                        }
                    }
                ]),
            GridViewColumn::for('title')
                ->label('wcf.global.title')
                ->sortable(sortByDatabaseColumn: 'articleContent.title')
                ->filter($this->getArticleContentFilter())
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof ViewableArticle);

                            $labels = '';
                            if ($row->hasLabels()) {
                                $labels = '<ul class="labelList">';
                                foreach ($row->getLabels() as $label) {
                                    $labels .= '<li>' . $label->render() . '</li>';
                                }
                                $labels .= '</ul>';
                            }
                            $badges = '';
                            if ($row->isDeleted) {
                                $badges .= \sprintf(
                                    '<span class="badge label red">%s</span>',
                                    WCF::getLanguage()->get('wcf.message.status.deleted')
                                );
                            }
                            if ($row->publicationStatus === Article::UNPUBLISHED) {
                                $badges .= \sprintf(
                                    '<span class="badge">%s</span>',
                                    WCF::getLanguage()->get('wcf.acp.article.publicationStatus.unpublished')
                                );
                            }
                            if ($row->publicationStatus === Article::DELAYED_PUBLICATION) {
                                $dateTime = \IntlDateFormatter::formatObject(
                                    WCF::getUser()->getLocalDate($row->publicationDate),
                                    [
                                        \IntlDateFormatter::LONG,
                                        \IntlDateFormatter::SHORT,
                                    ],
                                    WCF::getLanguage()->getLocale()
                                );

                                $badges .= \sprintf(
                                    '<span class="badge" title="%s">%s</span>',
                                    $dateTime,
                                    WCF::getLanguage()->get('wcf.acp.article.publicationStatus.delayed')
                                );
                            }

                            // @phpstan-ignore property.notFound
                            $articleTitle = StringUtil::encodeHTML($row->title);

                            return \sprintf('%s %s%s', $badges, $articleTitle, $labels);
                        }
                    },
                ])
                ->titleColumn(),
            GridViewColumn::for('content')
                ->label('wcf.acp.article.content')
                ->filter($this->getArticleContentFilter())
                ->hidden(),
            GridViewColumn::for('userID')
                ->label('wcf.user.username')
                ->renderer(new UserLinkColumnRenderer(UserEditForm::class))
                ->sortable(sortByDatabaseColumn: 'username')
                ->filter(new UserFilter()),
            GridViewColumn::for('categoryID')
                ->label('wcf.global.category')
                ->sortable()
                ->renderer(new CategoryColumnRenderer())
                ->filter(new CategoryFilter((new CategoryNodeTree('com.woltlab.wcf.article.category'))->getIterator())),
            GridViewColumn::for('publicationStatus')
                ->label('wcf.acp.article.publicationStatus')
                ->filter(
                    new SelectFilter([
                        0 => 'wcf.acp.article.publicationStatus.unpublished',
                        1 => 'wcf.acp.article.publicationStatus.published',
                        2 => 'wcf.acp.article.publicationStatus.delayed',
                    ])
                )
                ->hidden(),
            GridViewColumn::for('views')
                ->label('wcf.acp.article.views')
                ->renderer(new NumberColumnRenderer())
                ->sortable(),
            GridViewColumn::for('time')
                ->label('wcf.acp.sessionLog.time')
                ->sortable()
                ->renderer(new TimeColumnRenderer())
                ->filter(new TimeFilter()),
            GridViewColumn::for('isDeleted')
                ->label('wcf.acp.article.isDeleted')
                ->filter(new BooleanFilter())
                ->hidden(),
        ]);

        $provider = new ArticleInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(ArticleEditForm::class)
        ]);
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new ArticleBulkInteractions());

        $this->setSortField('time');
        $this->setSortOrder('DESC');
        $this->addRowLink(new GridViewRowLink(ArticleEditForm::class));
    }

    private function getArticleContentFilter(): TextFilter
    {
        return new class() extends TextFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
            {
                $list->getConditionBuilder()->add(
                    "article.articleID IN (
                                  SELECT  articleID
                                  FROM    wcf1_article_content
                                  WHERE   {$id} LIKE ?
                              )",
                    ['%' . WCF::getDB()->escapeLikeValue($value) . '%']
                );
            }
        };
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \MODULE_ARTICLE
            && (
                WCF::getSession()->getPermission('admin.content.article.canManageArticle')
                || WCF::getSession()->getPermission('admin.content.article.canManageOwnArticles')
                || WCF::getSession()->getPermission('admin.content.article.canContributeArticle')
            );
    }

    #[\Override]
    protected function createObjectList(): AccessibleArticleList
    {
        $list = new AccessibleArticleList();
        $join = ' LEFT JOIN wcf1_article_content articleContent
                        ON (
                                articleContent.articleID = article.articleID
                            AND (
                                   articleContent.languageID IS NULL
                                OR articleContent.languageID = ' . WCF::getLanguage()->languageID . '
                            )
                        )';
        $list->sqlJoins .= $join;
        $list->sqlConditionJoins .= $join;

        if (!empty($list->sqlSelects)) {
            $list->sqlSelects .= ', ';
        }

        $list->sqlSelects .= "articleContent.title";

        return $list;
    }

    #[\Override]
    protected function getInitializedEvent(): ArticleGridViewInitialized
    {
        return new ArticleGridViewInitialized($this);
    }
}
