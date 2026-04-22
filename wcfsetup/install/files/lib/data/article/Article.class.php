<?php

namespace wcf\data\article;

use wcf\data\article\category\ArticleCategory;
use wcf\data\article\content\ArticleContent;
use wcf\data\attachment\Attachment;
use wcf\data\CollectionDatabaseObject;
use wcf\data\ILinkableObject;
use wcf\data\IPopoverObject;
use wcf\data\IUserContent;
use wcf\data\label\Label;
use wcf\data\media\ViewableMedia;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\system\article\discussion\CommentArticleDiscussionProvider;
use wcf\system\article\discussion\IArticleDiscussionProvider;
use wcf\system\article\discussion\VoidArticleDiscussionProvider;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\reaction\ReactionData;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a cms article.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $articleID          unique id of the article
 * @property-read   ?int    $userID             id of the user the article belongs to or `null` if the user does not exist anymore
 * @property-read   string  $username           name of the user the article belongs to
 * @property-read   int     $time               timestamp at which the comment has been written
 * @property-read   int     $categoryID         id of the category the article belongs to
 * @property-read   0|1     $isMultilingual     is `1` if the article is available in multiple languages, otherwise `0`
 * @property-read   int     $publicationStatus  publication status of the article (see `Article::UNPUBLISHED`, `Article::PUBLISHED` and `Article::DELAYED_PUBLICATION`)
 * @property-read   int     $publicationDate    timestamp at which the article will be automatically published or `0` if it has already been published
 * @property-read   0|1     $enableComments     is `1` if comments are enabled for the article, otherwise `0`
 * @property-read   int     $views              number of times the article has been viewed
 * @property-read   int     $cumulativeLikes    cumulative result of likes for the article
 * @property-read   int     $attachments        number of attachments in the article descriptions
 * @property-read   0|1     $isDeleted          is 1 if the article is in trash bin, otherwise 0
 * @property-read   0|1     $hasLabels          is `1` if labels are assigned to the article
 *
 * @extends CollectionDatabaseObject<ArticleCollection>
 */
class Article extends CollectionDatabaseObject implements ILinkableObject, IPopoverObject, IUserContent
{
    /**
     * indicates that article is unpublished
     */
    const UNPUBLISHED = 0;

    /**
     * indicates that article is published
     */
    const PUBLISHED = 1;

    /**
     * indicates that the publication of an article is delayed
     */
    const DELAYED_PUBLICATION = 2;

    protected int $effectiveVisitTime;

    /**
     * Returns true if the active user can delete this article.
     */
    public function canDelete(): bool
    {
        if (WCF::getSession()->hasPermission('admin.content.article.canManageArticle')) {
            return true;
        }

        if (WCF::getSession()->hasPermission('admin.content.article.canManageOwnArticles') && $this->userID == WCF::getUser()->userID) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the given user has access to this article. If the given $user is null,
     * the function uses the current user.
     *
     * <strong>Attention:</strong> The `$user` parameter was introduced with version 5.5.
     */
    public function canRead(?UserProfile $user = null): bool
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        if ($this->isDeleted) {
            if (
                !$user->getPermission('admin.content.article.canManageArticle')
                && !($user->getPermission('admin.content.article.canManageOwnArticles') && $this->userID == $user->userID)
            ) {
                return false;
            }
        }

        if ($this->publicationStatus != self::PUBLISHED) {
            if (
                !$user->getPermission('admin.content.article.canManageArticle')
                && !($user->getPermission('admin.content.article.canManageOwnArticles') && $this->userID == $user->userID)
                && !($user->getPermission('admin.content.article.canContributeArticle') && $this->userID == $user->userID)
            ) {
                return false;
            }
        }

        if ($this->getCategory()) {
            return $this->getCategory()->isAccessible($user->getDecoratedObject());
        }

        return $user->getPermission('user.article.canRead');
    }

    /**
     * Returns true if the current user can edit these article.
     *
     * @since       5.2
     */
    public function canEdit(): bool
    {
        if (!$this->canRead()) {
            return false;
        }

        if (WCF::getSession()->hasPermission('admin.content.article.canManageArticle')) {
            return true;
        }

        if (WCF::getSession()->hasPermission('admin.content.article.canManageOwnArticles') && $this->userID == WCF::getUser()->userID) {
            return true;
        }

        if ($this->publicationStatus != self::PUBLISHED) {
            if (WCF::getSession()->hasPermission('admin.content.article.canContributeArticle') && $this->userID == WCF::getUser()->userID) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the current user can publish these article.
     *
     * @since       5.2
     */
    public function canPublish(): bool
    {
        if (WCF::getSession()->hasPermission('admin.content.article.canManageArticle')) {
            return true;
        }

        if (WCF::getSession()->hasPermission('admin.content.article.canManageOwnArticles') && $this->userID == WCF::getUser()->userID) {
            return true;
        }

        return false;
    }

    #[\Override]
    public function getLink(): string
    {
        if ($this->getArticleContent() !== null) {
            return $this->getArticleContent()->getLink();
        }

        return '';
    }

    #[\Override]
    public function getTitle(): string
    {
        if ($this->getArticleContent() !== null) {
            return $this->getArticleContent()->getTitle();
        }

        return '';
    }

    /**
     * Returns the article's unformatted teaser.
     */
    public function getTeaser(): string
    {
        if ($this->getArticleContent() !== null) {
            return $this->getArticleContent()->getTeaser();
        }

        return '';
    }

    /**
     * Returns the article's formatted teaser.
     */
    public function getFormattedTeaser(): string
    {
        if ($this->getArticleContent() !== null) {
            return $this->getArticleContent()->getFormattedTeaser();
        }

        return '';
    }

    /**
     * Returns the article's formatted content.
     */
    public function getFormattedContent(): string
    {
        if ($this->getArticleContent() !== null) {
            return $this->getArticleContent()->getFormattedContent();
        }

        return '';
    }

    /**
     * Returns the active content version.
     */
    public function getArticleContent(): ?ArticleContent
    {
        return $this->getCollection()->getArticleContent($this);
    }

    /**
     * Returns the article's content.
     *
     * @return  ArticleContent[]
     */
    public function getArticleContents(): array
    {
        return $this->getCollection()->getArticleContents($this);
    }

    /**
     * Returns the article's language links.
     *
     * @return ArticleContent[]
     * @deprecated 6.3 Use `getArticleContents()` instead.
     */
    public function getLanguageLinks(): array
    {
        return $this->getArticleContents();
    }

    /**
     * Returns the category of the article.
     *
     * @return ?ArticleCategory
     */
    public function getCategory(): ?ArticleCategory
    {
        return ArticleCategory::getCategory($this->categoryID);
    }

    /**
     * Returns the responsible discussion provider for this article.
     *
     * @since 5.2
     */
    public function getDiscussionProvider(): IArticleDiscussionProvider
    {
        $discussionProvider = $this->getCollection()->getDiscussionProvider($this);
        if ($discussionProvider === null) {
            throw new \RuntimeException('No discussion provider has claimed to be responsible for the article #' . $this->articleID);
        }

        return $discussionProvider;
    }

    /**
     * Returns the list of the available discussion providers.
     *
     * @return string[]
     * @since 5.2
     */
    public static function getAllDiscussionProviders(): array
    {
        /** @var ?string[] $discussionProviders */
        static $discussionProviders;

        if ($discussionProviders === null) {
            $discussionProviders = [];

            $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.article.discussionProvider');
            $commentProvider = '';
            foreach ($objectTypes as $objectType) {
                // the comment and the "void" provider should always be the last in the list
                if ($objectType->className === CommentArticleDiscussionProvider::class) {
                    $commentProvider = $objectType->className;
                    continue;
                }

                $discussionProviders[] = $objectType->className;
            }

            $discussionProviders[] = $commentProvider;
            $discussionProviders[] = VoidArticleDiscussionProvider::class;
        }

        return $discussionProviders;
    }

    /**
     * @since 5.2
     */
    #[\Override]
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @since 5.2
     */
    #[\Override]
    public function getUserID(): ?int
    {
        return $this->userID;
    }

    /**
     * @since 5.2
     */
    #[\Override]
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return Attachment[]
     * @since 6.0
     */
    public function getAttachments(): array
    {
        return $this->getCollection()->getAttachments($this);
    }

    #[\Override]
    public function getPopoverLinkClass(): string
    {
        return 'articleLink';
    }

    /**
     * @since 6.3
     */
    public function canReact(): bool
    {
        return \MODULE_LIKE
            && \ARTICLE_ENABLE_LIKE
            && WCF::getUser()->userID
            && $this->userID != WCF::getUser()->userID
            && WCF::getSession()->hasPermission('user.like.canLike');
    }

    /**
     * @since 6.3
     */
    public function getCachedReactions(): ?string
    {
        return $this->getCollection()->getCachedReactions($this);
    }

    /**
     * @since 6.3
     */
    public function getReactionData(): ReactionData
    {
        return $this->getCollection()->getReactionData($this);
    }

    /**
     * Returns article owner's user profile.
     *
     * @since 6.3
     */
    public function getUserProfile(): UserProfile
    {
        return $this->getCollection()->getUserProfile($this);
    }

    /**
     * Returns the article's image.
     *
     * @since 6.3
     */
    public function getImage(): ?ViewableMedia
    {
        if ($this->getArticleContent() !== null) {
            return $this->getArticleContent()->getImage();
        }

        return null;
    }

    /**
     * Returns the article's teaser image.
     *
     * @since 6.3
     */
    public function getTeaserImage(): ?ViewableMedia
    {
        if ($this->getArticleContent() !== null) {
            return $this->getArticleContent()->getTeaserImage();
        }

        return null;
    }

    /**
     * Returns true if one or more labels are assigned to this article.
     *
     * @since 6.3
     * @deprecated 6.3 Use `hasLabels` property instead
     */
    public function hasLabels(): bool
    {
        return $this->hasLabels === 1;
    }

    /**
     * @return Label[]
     * @since 6.3
     */
    public function getLabels(): array
    {
        return $this->getCollection()->getLabels($this);
    }

    /**
     * @since 6.3
     */
    public function isPublished(): bool
    {
        return $this->publicationStatus === Article::PUBLISHED;
    }

    /**
     * @since 6.3
     */
    public function getVisitTime(): int
    {
        return $this->getCollection()->getVisitTime($this);
    }

    /**
     * @since 6.3
     */
    public function isNew(): bool
    {
        return $this->time > $this->getEffectiveVisitTime();
    }

    /**
     * @since 6.3
     */
    public function getEffectiveVisitTime(): int
    {
        if (!isset($this->effectiveVisitTime)) {
            if (WCF::getUser()->userID !== 0) {
                $this->effectiveVisitTime = \max(
                    0,
                    $this->getVisitTime(),
                    VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.article')
                );
            } else {
                $this->effectiveVisitTime = \TIME_NOW;
            }
        }

        return $this->effectiveVisitTime;
    }

    /**
     * Returns the number of unread articles.
     *
     * @since 6.3
     */
    public static function getUnreadArticles(): int
    {
        if (WCF::getUser()->isGuest()) {
            return 0;
        }

        static $unreadArticles = null;

        if ($unreadArticles === null) {
            $unreadArticles = UserStorageHandler::getInstance()->getField('unreadArticles');

            // cache does not exist or is outdated
            if ($unreadArticles === null) {
                $unreadArticles = 0;
                $categoryIDs = ArticleCategory::getAccessibleCategoryIDs();
                if ($categoryIDs !== []) {
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
                    $unreadArticles = $statement->fetchSingleColumn();
                }

                // update storage unreadEntries
                UserStorageHandler::getInstance()->update(
                    WCF::getUser()->userID,
                    'unreadArticles',
                    $unreadArticles
                );
            }
        }

        return $unreadArticles;
    }
}
