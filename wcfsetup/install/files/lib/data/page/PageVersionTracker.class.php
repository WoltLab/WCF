<?php

namespace wcf\data\page;

use wcf\acp\form\PageEditForm;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IVersionTrackerObject;
use wcf\data\page\content\PageContent;
use wcf\system\request\LinkHandler;

/**
 * Represents a page with version tracking.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   Page
 * @extends DatabaseObjectDecorator<Page>
 */
class PageVersionTracker extends DatabaseObjectDecorator implements IVersionTrackerObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Page::class;

    /**
     * list of page content objects
     * @var PageContent[]
     */
    protected $content = [];

    #[\Override]
    public function getObjectID()
    {
        return $this->getDecoratedObject()->pageID;
    }

    /**
     * Adds an page content object as child.
     *
     * @param PageContent $content page content object
     * @return void
     */
    public function addContent(PageContent $content)
    {
        $this->content[] = $content;
    }

    /**
     * Sets the list of page content objects.
     *
     * @param PageContent[] $content page content objects
     * @return void
     */
    public function setContent(array $content)
    {
        $this->content = $content;
    }

    /**
     * Returns the list of stored page content objects.
     *
     * @return PageContent[]   stored page content objects
     */
    public function getContent()
    {
        return $this->content;
    }

    #[\Override]
    public function getLink(): string
    {
        return $this->getDecoratedObject()->getLink();
    }

    #[\Override]
    public function getUsername()
    {
        return '';
    }

    #[\Override]
    public function getUserID()
    {
        return 0;
    }

    #[\Override]
    public function getTime()
    {
        return 0;
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->getDecoratedObject()->getTitle();
    }

    #[\Override]
    public function getEditLink()
    {
        return LinkHandler::getInstance()->getControllerLink(
            PageEditForm::class,
            ['id' => $this->getDecoratedObject()->pageID]
        );
    }
}
