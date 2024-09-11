<?php

namespace wcf\data\article\content;

use wcf\data\article\Article;
use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\data\language\Language;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;

/**
 * Represents an article content.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @property-read   int $articleContentID   unique id of the article content
 * @property-read   int $articleID      id of the article the article content belongs to
 * @property-read   int $languageID     id of the article content's language
 * @property-read   string $title          title of the article in the associated language
 * @property-read   string $content        actual content of the article in the associated language
 * @property-read   string $teaser         teaser of the article in the associated language or empty if no teaser exists
 * @property-read   int|null $imageID        id of the (image) media object used as article image for the associated language or `null` if no image is used
 * @property-read   int|null $teaserImageID          id of the (image) media object used as article teaser image for the associated language or `null` if no image is used
 * @property-read   int $hasEmbeddedObjects is `1` if there are embedded objects in the article content, otherwise `0`
 * @property-read       string $metaTitle              title of the article used in the title tag
 * @property-read       string $metaDescription        meta description of the article
 * @property-read   int $comments       number of comments
 */
class ArticleContent extends DatabaseObject implements ILinkableObject, IRouteController
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'articleContentID';

    /**
     * article object
     * @var Article
     */
    protected $article;

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getLink('Article', [
            'object' => $this,
            'forceFrontend' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Returns the article's unformatted teaser.
     *
     * @return      string
     */
    public function getTeaser()
    {
        return $this->teaser;
    }

    /**
     * Returns the article's formatted teaser.
     *
     * @return      string
     */
    public function getFormattedTeaser()
    {
        if ($this->teaser) {
            return \nl2br(StringUtil::encodeHTML($this->teaser), false);
        } else {
            return MessageUtil::truncateFormattedMessage($this->getSimplifiedFormattedContent(), 500);
        }
    }

    /**
     * Returns the article's formatted content.
     *
     * @return      string
     */
    public function getFormattedContent()
    {
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
     * @since 6.1
     */
    public function getSimplifiedFormattedContent(): string
    {
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

    /**
     * Returns article object.
     *
     * @return Article
     */
    public function getArticle()
    {
        if ($this->article === null) {
            $this->article = new Article($this->articleID);
        }

        return $this->article;
    }

    /**
     * Returns the language of this article content or `null` if no language has been specified.
     *
     * @return  Language|null
     */
    public function getLanguage()
    {
        if ($this->languageID) {
            return LanguageFactory::getInstance()->getLanguage($this->languageID);
        }

        return null;
    }

    /**
     * Returns a version of this message optimized for use in emails.
     *
     * @param string $mimeType Either 'text/plain' or 'text/html'
     * @return  string
     * @since       5.2
     */
    public function getMailText($mimeType = 'text/plain')
    {
        if ($this->hasEmbeddedObjects) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects(
                'com.woltlab.wcf.article.content',
                [$this->articleContentID]
            );
        }

        switch ($mimeType) {
            case 'text/plain':
                $processor = new HtmlOutputProcessor();
                $processor->setOutputType('text/plain');
                $processor->enableUgc = false;
                $processor->process($this->content, 'com.woltlab.wcf.article.content', $this->articleContentID);

                return $processor->getHtml();
            case 'text/html':
                // parse and return message
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
     *
     * @param int $articleID
     * @param int $languageID
     * @return      ArticleContent|null
     */
    public static function getArticleContent($articleID, $languageID)
    {
        if ($languageID !== null) {
            $sql = "SELECT  *
                    FROM    wcf" . WCF_N . "_article_content
                    WHERE   articleID = ?
                        AND languageID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$articleID, $languageID]);
        } else {
            $sql = "SELECT  *
                    FROM    wcf" . WCF_N . "_article_content
                    WHERE   articleID = ?
                        AND languageID IS NULL";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$articleID]);
        }

        if (($row = $statement->fetchSingleRow()) !== false) {
            return new self(null, $row);
        }

        return null;
    }
}
