<?php

namespace wcf\data\article;

use wcf\data\article\category\ArticleCategory;
use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ViewableArticleContent;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\label\Label;
use wcf\data\media\ViewableMedia;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a viewable article.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method          Article                 getDecoratedObject()
 * @method          ArticleContent|ViewableArticleContent   getArticleContent()
 * @mixin           Article
 * @property-read   int|null $visitTime  last time the active user has visited the time or `null` if object has not been fetched via `ViewableArticleList` or if the active user is a guest
 */
class ViewableArticle extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Article::class;

    /**
     * user profile object
     * @var UserProfile
     */
    protected $userProfile;

    /**
     * effective visit time
     * @var int
     */
    protected $effectiveVisitTime;

    /**
     * number of unread articles
     * @var int
     */
    protected static $unreadArticles;

    /**
     * number of unread articles in watched categories
     * @var int
     * @since   5.2
     */
    protected static $unreadWatchedArticles;

    /**
     * number of unread articles ordered by categories
     * @var int[]
     * @since   5.2
     */
    protected static $unreadArticlesByCategory;

    /**
     * list of assigned labels
     * @var Label[]
     */
    protected $labels = [];

    /**
     * Returns a specific article decorated as viewable article or `null` if it does not exist.
     *
     * @param int $articleID
     * @param bool $enableContentLoading Enables/disables the loading of article content objects
     * @return  ViewableArticle
     */
    public static function getArticle($articleID, $enableContentLoading = true)
    {
        $list = new ViewableArticleList();
        $list->enableContentLoading($enableContentLoading);
        $list->setObjectIDs([$articleID]);
        $list->readObjects();

        return $list->getSingleObject();
    }

    /**
     * Returns the user profile object.
     *
     * @return  UserProfile
     */
    public function getUserProfile()
    {
        if ($this->userProfile === null) {
            if ($this->userID) {
                $this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
            } else {
                $this->userProfile = new UserProfile(new User(null, [
                    'username' => $this->username,
                ]));
            }
        }

        return $this->userProfile;
    }

    /**
     * Sets the article's content.
     *
     * @param ViewableArticleContent $articleContent
     */
    public function setArticleContent(ViewableArticleContent $articleContent)
    {
        if ($this->getDecoratedObject()->articleContents === null) {
            $this->getDecoratedObject()->articleContents = [];
        }

        $this->getDecoratedObject()->articleContents[$articleContent->languageID ?: 0] = $articleContent;
    }

    /**
     * Returns the article's image.
     *
     * @return  ViewableMedia|null
     */
    public function getImage()
    {
        if ($this->getArticleContent() !== null) {
            return $this->getArticleContent()->getImage();
        }

        return null;
    }

    /**
     * Returns the article's teaser image.
     *
     * @return  ViewableMedia|null
     */
    public function getTeaserImage()
    {
        if ($this->getArticleContent() !== null) {
            return $this->getArticleContent()->getTeaserImage();
        }

        return null;
    }

    /**
     * Returns the effective visit time.
     *
     * @return  int
     */
    public function getVisitTime()
    {
        if ($this->effectiveVisitTime === null) {
            if (WCF::getUser()->userID) {
                $this->effectiveVisitTime = \max(
                    $this->visitTime,
                    VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.article')
                );
            } else {
                $this->effectiveVisitTime = \max(VisitTracker::getInstance()->getObjectVisitTime(
                    'com.woltlab.wcf.article',
                    $this->articleID
                ), VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.article'));
            }
            if ($this->effectiveVisitTime === null) {
                $this->effectiveVisitTime = 0;
            }
        }

        return $this->effectiveVisitTime;
    }

    /**
     * Returns true if this article is new for the active user.
     *
     * @return  bool
     */
    public function isNew()
    {
        return $this->time > $this->getVisitTime();
    }

    /**
     * Adds a label.
     *
     * @param Label $label
     */
    public function addLabel(Label $label)
    {
        $this->labels[$label->labelID] = $label;
    }

    /**
     * Returns a list of labels.
     *
     * @return  Label[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Returns true if one or more labels are assigned to this article.
     *
     * @return  bool
     */
    public function hasLabels()
    {
        return !empty($this->labels);
    }

    /**
     * @return bool
     * @since 5.2
     */
    public function isPublished()
    {
        return $this->publicationStatus == Article::PUBLISHED;
    }

    /**
     * Returns the number of unread articles.
     *
     * @return  int
     */
    public static function getUnreadArticles()
    {
        if (self::$unreadArticles === null) {
            self::$unreadArticles = 0;

            if (WCF::getUser()->userID) {
                $unreadArticles = UserStorageHandler::getInstance()->getField('unreadArticles');

                // cache does not exist or is outdated
                if ($unreadArticles === null) {
                    $categoryIDs = ArticleCategory::getAccessibleCategoryIDs();
                    if (!empty($categoryIDs)) {
                        $conditionBuilder = new PreparedStatementConditionBuilder();
                        $conditionBuilder->add('article.categoryID IN (?)', [$categoryIDs]);
                        $conditionBuilder->add(
                            'article.time > ?',
                            [VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.article')]
                        );
                        $conditionBuilder->add('article.isDeleted = ?', [0]);
                        $conditionBuilder->add('article.publicationStatus = ?', [Article::PUBLISHED]);
                        $conditionBuilder->add('(article.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)');

                        $sql = "SELECT      COUNT(*)
                                FROM        wcf1_article article
                                LEFT JOIN   wcf1_tracked_visit tracked_visit
                                ON          tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.woltlab.wcf.article') . "
                                        AND tracked_visit.objectID = article.articleID
                                        AND tracked_visit.userID = " . WCF::getUser()->userID . "
                                " . $conditionBuilder;
                        $statement = WCF::getDB()->prepare($sql);
                        $statement->execute($conditionBuilder->getParameters());
                        self::$unreadArticles = $statement->fetchSingleColumn();
                    }

                    // update storage unreadEntries
                    UserStorageHandler::getInstance()->update(
                        WCF::getUser()->userID,
                        'unreadArticles',
                        self::$unreadArticles
                    );
                } else {
                    self::$unreadArticles = $unreadArticles;
                }
            }
        }

        return self::$unreadArticles;
    }

    /**
     * Returns the number of unread articles for a specific category.
     *
     * @param int $articleCategoryID
     * @return  int
     * @since       5.2
     */
    public static function getUnreadArticlesForCategory($articleCategoryID)
    {
        if (self::$unreadArticlesByCategory === null) {
            self::$unreadArticlesByCategory = [];

            if (WCF::getUser()->userID) {
                $unreadArticlesByCategory = UserStorageHandler::getInstance()->getField('unreadArticlesByCategory');

                // cache does not exist or is outdated
                if ($unreadArticlesByCategory === null) {
                    self::$unreadArticlesByCategory[$articleCategoryID] = self::fetchUnreadArticlesForCategory($articleCategoryID);

                    // update storage unreadEntries
                    UserStorageHandler::getInstance()->update(
                        WCF::getUser()->userID,
                        'unreadArticlesByCategory',
                        \serialize(self::$unreadArticlesByCategory)
                    );
                } else {
                    $unreadArticlesByCategory = \unserialize($unreadArticlesByCategory);

                    if (isset($unreadArticlesByCategory[$articleCategoryID])) {
                        self::$unreadArticlesByCategory = $unreadArticlesByCategory;
                    } else {
                        self::$unreadArticlesByCategory[$articleCategoryID] = self::fetchUnreadArticlesForCategory($articleCategoryID);

                        // update storage unreadEntries
                        UserStorageHandler::getInstance()->update(
                            WCF::getUser()->userID,
                            'unreadArticlesByCategory',
                            \serialize(self::$unreadArticlesByCategory)
                        );
                    }
                }
            } else {
                self::$unreadArticlesByCategory[$articleCategoryID] = 0;
            }
        } elseif (!isset(self::$unreadArticlesByCategory[$articleCategoryID])) {
            if (WCF::getUser()->userID) {
                self::$unreadArticlesByCategory[$articleCategoryID] = self::fetchUnreadArticlesForCategory($articleCategoryID);

                // update storage unreadEntries
                UserStorageHandler::getInstance()->update(
                    WCF::getUser()->userID,
                    'unreadArticlesByCategory',
                    \serialize(self::$unreadArticlesByCategory)
                );
            } else {
                self::$unreadArticlesByCategory[$articleCategoryID] = 0;
            }
        }

        return self::$unreadArticlesByCategory[$articleCategoryID];
    }

    /**
     * Returns the unread article count for a specific category.
     *
     * @param int $articleCategoryID
     * @return      int
     * @since       5.2
     */
    private static function fetchUnreadArticlesForCategory($articleCategoryID)
    {
        $accessibleCategoryIDs = ArticleCategory::getAccessibleCategoryIDs();

        if (!\in_array($articleCategoryID, $accessibleCategoryIDs)) {
            // the category is not accessible
            return 0;
        }

        $category = ArticleCategory::getCategory($articleCategoryID);

        if ($category === null) {
            throw new \InvalidArgumentException('The given article category id "' . $articleCategoryID . '" is not valid.');
        }

        $categoryIDs = \array_intersect(\array_merge(\array_map(static function ($category) {
            /** @var ArticleCategory $category */
            return $category->categoryID;
        }, $category->getChildCategories()), [$articleCategoryID]), $accessibleCategoryIDs);

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('article.categoryID IN (?)', [$categoryIDs]);
        $conditionBuilder->add(
            'article.time > ?',
            [VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.article')]
        );
        $conditionBuilder->add('article.isDeleted = ?', [0]);
        $conditionBuilder->add('article.publicationStatus = ?', [Article::PUBLISHED]);
        $conditionBuilder->add('(article.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)');

        $sql = "SELECT      COUNT(*)
                FROM        wcf1_article article
                LEFT JOIN   wcf1_tracked_visit tracked_visit
                ON          tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.woltlab.wcf.article') . "
                        AND tracked_visit.objectID = article.articleID
                        AND tracked_visit.userID = " . WCF::getUser()->userID . "
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchSingleColumn();
    }

    /**
     * Returns the number of unread articles in watched categories.
     *
     * @return  int
     * @since       5.2
     */
    public static function getWatchedUnreadArticles()
    {
        if (self::$unreadWatchedArticles === null) {
            self::$unreadWatchedArticles = 0;

            if (WCF::getUser()->userID) {
                $unreadArticles = UserStorageHandler::getInstance()->getField('unreadWatchedArticles');

                // cache does not exist or is outdated
                if ($unreadArticles === null) {
                    $categoryIDs = ArticleCategory::getSubscribedCategoryIDs();
                    if (!empty($categoryIDs)) {
                        $conditionBuilder = new PreparedStatementConditionBuilder();
                        $conditionBuilder->add('article.categoryID IN (?)', [$categoryIDs]);
                        $conditionBuilder->add(
                            'article.time > ?',
                            [VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.article')]
                        );
                        $conditionBuilder->add('article.isDeleted = ?', [0]);
                        $conditionBuilder->add('article.publicationStatus = ?', [Article::PUBLISHED]);
                        $conditionBuilder->add('(article.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)');

                        $sql = "SELECT      COUNT(*)
                                FROM        wcf1_article article
                                LEFT JOIN   wcf1_tracked_visit tracked_visit
                                ON          tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.woltlab.wcf.article') . "
                                        AND tracked_visit.objectID = article.articleID
                                        AND tracked_visit.userID = " . WCF::getUser()->userID . "
                                " . $conditionBuilder;
                        $statement = WCF::getDB()->prepare($sql);
                        $statement->execute($conditionBuilder->getParameters());
                        self::$unreadWatchedArticles = $statement->fetchSingleColumn();
                    }

                    // update storage unreadEntries
                    UserStorageHandler::getInstance()->update(
                        WCF::getUser()->userID,
                        'unreadWatchedArticles',
                        self::$unreadWatchedArticles
                    );
                } else {
                    self::$unreadWatchedArticles = $unreadArticles;
                }
            }
        }

        return self::$unreadWatchedArticles;
    }
}
