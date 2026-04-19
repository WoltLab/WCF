<?php

namespace wcf\data\article;

use wcf\acp\form\ArticleEditForm;
use wcf\data\article\content\ArticleContent;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IVersionTrackerObject;
use wcf\system\request\LinkHandler;

/**
 * Represents an article with version tracking.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   Article
 * @extends DatabaseObjectDecorator<Article>
 */
class ArticleVersionTracker extends DatabaseObjectDecorator implements IVersionTrackerObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Article::class;

    /**
     * list of article content objects
     * @var ArticleContent[]
     */
    protected $content = [];

    #[\Override]
    public function getObjectID()
    {
        return $this->getDecoratedObject()->articleID;
    }

    /**
     * Adds an article content object as child.
     *
     * @param ArticleContent $content article content object
     * @return void
     */
    public function addContent(ArticleContent $content)
    {
        $this->content[] = $content;
    }

    /**
     * Sets the list of article content objects.
     *
     * @param ArticleContent[] $content article content objects
     * @return void
     */
    public function setContent(array $content)
    {
        $this->content = $content;
    }

    /**
     * Returns the list of stored article content objects.
     *
     * @return      ArticleContent[]        stored article content objects
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
        return $this->getDecoratedObject()->username;
    }

    #[\Override]
    public function getUserID()
    {
        return $this->getDecoratedObject()->userID;
    }

    #[\Override]
    public function getTime()
    {
        return $this->getDecoratedObject()->time;
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
            ArticleEditForm::class,
            ['id' => $this->getDecoratedObject()->articleID]
        );
    }
}
