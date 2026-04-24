<?php

namespace wcf\data\article\content;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\search\ISearchResultObject;
use wcf\page\ArticlePage;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchResultTextParser;

/**
 * Represents an article content as a search result.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   ArticleContent
 * @extends DatabaseObjectDecorator<ArticleContent>
 */
class SearchResultArticleContent extends DatabaseObjectDecorator implements ISearchResultObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ArticleContent::class;


    #[\Override]
    public function getUserProfile()
    {
        return $this->getArticle()->getUserProfile();
    }

    #[\Override]
    public function getSubject()
    {
        return $this->getDecoratedObject()->getTitle();
    }

    #[\Override]
    public function getTime()
    {
        return $this->getArticle()->time;
    }

    #[\Override]
    public function getLink(string $query = ''): string
    {
        if ($query !== '') {
            return LinkHandler::getInstance()->getControllerLink(
                ArticlePage::class,
                [
                    'object' => $this->getDecoratedObject(),
                    'highlight' => \urlencode($query),
                ]
            );
        }

        return $this->getDecoratedObject()->getLink();
    }

    #[\Override]
    public function getObjectTypeName()
    {
        return 'com.woltlab.wcf.article';
    }

    #[\Override]
    public function getFormattedMessage()
    {
        $processor = new HtmlOutputProcessor();
        $processor->setOutputType('text/simplified-html');
        $processor->process(
            $this->content,
            'com.woltlab.wcf.article.content',
            $this->articleContentID,
            false,
            $this->languageID
        );
        $message = SearchResultTextParser::getInstance()->parse($processor->getHtml());

        if ($this->getDecoratedObject()->getTeaserImage() !== null) {
            return '<div class="box96">' . $this->getDecoratedObject()->getTeaserImage()->getElementTag(96) . '<div>' . $message . '</div></div>';
        }

        return $message;
    }

    #[\Override]
    public function getContainerTitle()
    {
        return '';
    }

    #[\Override]
    public function getContainerLink()
    {
        return '';
    }
}
