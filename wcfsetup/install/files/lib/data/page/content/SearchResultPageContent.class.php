<?php

namespace wcf\data\page\content;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\page\Page;
use wcf\data\search\ISearchResultObject;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchResultTextParser;

/**
 * Represents an page content as a search result.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   PageContent
 * @extends DatabaseObjectDecorator<PageContent>
 */
class SearchResultPageContent extends DatabaseObjectDecorator implements ISearchResultObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = PageContent::class;

    #[\Override]
    public function getUserProfile()
    {
        return null;
    }

    #[\Override]
    public function getSubject()
    {
        return $this->getDecoratedObject()->title;
    }

    #[\Override]
    public function getTime()
    {
        return 0;
    }

    #[\Override]
    public function getLink(string $query = '')
    {
        return LinkHandler::getInstance()->getCmsLink(
            $this->getDecoratedObject()->pageID,
            ($this->getDecoratedObject()->languageID ?: -1)
        );
    }

    #[\Override]
    public function getObjectTypeName()
    {
        return 'com.woltlab.wcf.page';
    }

    #[\Override]
    public function getFormattedMessage()
    {
        $page = new Page($this->pageID);
        if ($page->pageType === 'text') {
            $message = SearchResultTextParser::getInstance()->parse($this->getDecoratedObject()->getFormattedContent());
        } else {
            $message = SearchResultTextParser::getInstance()->parse($this->getDecoratedObject()->getParsedContent());
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
