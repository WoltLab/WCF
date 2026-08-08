<?php

namespace wcf\data\article\content;

use wcf\data\article\Article;
use wcf\data\attachment\Attachment;
use wcf\data\CollectionDatabaseObject;
use wcf\data\ILinkableObject;
use wcf\data\language\Language;
use wcf\data\media\ViewableMedia;
use wcf\page\ArticlePage;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;

/**
 * Represents an article content.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $articleContentID   unique id of the article content
 * @property-read   int     $articleID          id of the article the article content belongs to
 * @property-read   ?int    $languageID         id of the article content's language
 * @property-read   string  $title              title of the article in the associated language
 * @property-read   string  $slug               custom URL slug of the article in the associated language or empty to derive it from the title
 * @property-read   ?string $content            actual content of the article in the associated language
 * @property-read   ?string $teaser             teaser of the article in the associated language or empty if no teaser exists
 * @property-read   ?int    $imageID            id of the (image) media object used as article image for the associated language or `null` if no image is used
 * @property-read   ?int    $teaserImageID      id of the (image) media object used as article teaser image for the associated language or `null` if no image is used
 * @property-read   0|1     $hasEmbeddedObjects is `1` if there are embedded objects in the article content, otherwise `0`
 * @property-read   string  $metaTitle          title of the article used in the title tag
 * @property-read   string  $metaDescription    meta description of the article
 * @property-read   int     $comments           number of comments
 * @property-read   int     $attachments        number of attachments
 *
 * @extends CollectionDatabaseObject<ArticleContentCollection>
 */
class ArticleContent extends CollectionDatabaseObject implements ILinkableObject, IRouteController
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'articleContentID';

    #[\Override]
    public function getLink(): string
    {
        if ($this->slug !== '') {
            return LinkHandler::getInstance()->getControllerLink(ArticlePage::class, [
                'id' => $this->articleContentID,
                'title' => $this->slug,
            ]);
        }

        return LinkHandler::getInstance()->getControllerLink(ArticlePage::class, [
            'object' => $this,
        ]);
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Returns the article's unformatted teaser.
     */
    public function getTeaser(): string
    {
        return $this->teaser ?? '';
    }

    /**
     * Returns the article's formatted teaser.
     */
    public function getFormattedTeaser(): string
    {
        if ($this->teaser !== null && $this->teaser !== '') {
            return \nl2br(StringUtil::encodeHTML($this->teaser), false);
        } else {
            return MessageUtil::truncateFormattedMessage($this->getSimplifiedFormattedContent(), 500);
        }
    }

    /**
     * Returns the article's formatted content.
     */
    public function getFormattedContent(): string
    {
        $this->loadEmbeddedObjects();

        $processor = new HtmlOutputProcessor();
        $processor->enableUgc = false;
        $processor->process(
            $this->content,
            'com.woltlab.wcf.article.content',
            $this->articleContentID,
            false,
            $this->languageID
        );

        return $processor->getHtml();
    }

    /**
     * Returns a simplified version of the formatted content.
     *
     * @since 6.1
     */
    public function getSimplifiedFormattedContent(): string
    {
        $this->loadEmbeddedObjects();

        $htmlOutputProcessor = new HtmlOutputProcessor();
        $htmlOutputProcessor->setOutputType('text/simplified-html');
        $htmlOutputProcessor->enableUgc = false;
        $htmlOutputProcessor->process(
            $this->content,
            'com.woltlab.wcf.article.content',
            $this->articleContentID,
            false,
            $this->languageID
        );

        return $htmlOutputProcessor->getHtml();
    }

    public function getArticle(): Article
    {
        return $this->getCollection()->getArticle($this);
    }

    /**
     * Returns the language of this article content or `null` if no language has been specified.
     */
    public function getLanguage(): ?Language
    {
        if ($this->languageID !== null) {
            return LanguageFactory::getInstance()->getLanguage($this->languageID);
        }

        return null;
    }

    /**
     * Returns a version of this message optimized for use in emails.
     *
     * @param string $mimeType Either 'text/plain' or 'text/html'
     * @since 5.2
     */
    public function getMailText(string $mimeType = 'text/plain'): string
    {
        $this->loadEmbeddedObjects();

        switch ($mimeType) {
            case 'text/plain':
                $processor = new HtmlOutputProcessor();
                $processor->setOutputType('text/plain');
                $processor->enableUgc = false;
                $processor->process($this->content, 'com.woltlab.wcf.article.content', $this->articleContentID);

                return $processor->getHtml();
            case 'text/html':
                $processor = new HtmlOutputProcessor();
                $processor->setOutputType('text/simplified-html');
                $processor->enableUgc = false;
                $processor->process($this->content, 'com.woltlab.wcf.article.content', $this->articleContentID);

                return $processor->getHtml();
        }

        throw new \LogicException('Unreachable');
    }

    /**
     * Returns a certain article content or `null` if it does not exist.
     */
    public static function getArticleContent(int $articleID, ?int $languageID): ?ArticleContent
    {
        if ($languageID !== null) {
            $sql = "SELECT  *
                    FROM    wcf1_article_content
                    WHERE   articleID = ?
                        AND languageID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$articleID, $languageID]);
        } else {
            $sql = "SELECT  *
                    FROM    wcf1_article_content
                    WHERE   articleID = ?
                        AND languageID IS NULL";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$articleID]);
        }

        if (($row = $statement->fetchSingleRow()) !== false) {
            return new self(null, $row);
        }

        return null;
    }

    /**
     * @since 6.3
     */
    public function loadEmbeddedObjects(): void
    {
        $this->getCollection()->loadEmbeddedObjects('com.woltlab.wcf.article.content');
    }

    /**
     * Returns the article's image if the active user can access it or `null`.
     *
     * @since 6.3
     */
    public function getImage(): ?ViewableMedia
    {
        if ($this->imageID === null) {
            return null;
        }

        $image = $this->getCollection()->getImage($this->imageID);
        if ($image === null || !$image->isAccessible()) {
            return null;
        }

        return $image;
    }

    /**
     * Returns the article's teaser image if the active user can access it or `null`.
     *
     * @since 6.3
     */
    public function getTeaserImage(): ?ViewableMedia
    {
        if ($this->teaserImageID === null) {
            return $this->getImage();
        }

        $image = $this->getCollection()->getImage($this->teaserImageID);
        if ($image === null || !$image->isAccessible()) {
            return null;
        }

        return $image;
    }

    /**
     * @return Attachment[]
     * @since 6.3
     */
    public function getAttachments(): array
    {
        return $this->getCollection()->getAttachments($this);
    }

    /**
     * Returns the article content with the given slug, within the given language scope.
     * The `$excludedArticleID` is excluded from the lookup to allow updates of
     * an existing article.
     *
     * @since 6.3
     */
    public static function findBySlug(string $slug, ?int $languageID, ?int $excludedArticleID = null): ?ArticleContent
    {
        if ($slug === '') {
            return null;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('slug = ?', [$slug]);

        if ($languageID === null) {
            $conditionBuilder->add('languageID IS NULL');
        } else {
            $conditionBuilder->add('(languageID = ? OR languageID IS NULL)', [$languageID]);
        }
        if ($excludedArticleID !== null) {
            $conditionBuilder->add('articleID <> ?', [$excludedArticleID]);
        }

        $sql = "SELECT  *
                FROM    wcf1_article_content
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchSingleObject(ArticleContent::class);
    }
}
