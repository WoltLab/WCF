<?php

namespace wcf\data\article\content;

use wcf\data\search\ISearchResultObject;
use wcf\page\ArticlePage;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchResultTextParser;

/**
 * Represents an article content as a search result.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SearchResultArticleContent extends ViewableArticleContent implements ISearchResultObject
{
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
        $parameters = [
            'object' => $this->getDecoratedObject(),
        ];

        if ($query) {
            $parameters['highlight'] = \urlencode($query);
        }

        return LinkHandler::getInstance()->getControllerLink(ArticlePage::class, $parameters);
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

        if ($this->getTeaserImage()) {
            return '<div class="box96">' . $this->getTeaserImage()->getElementTag(96) . '<div>' . $message . '</div></div>';
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
