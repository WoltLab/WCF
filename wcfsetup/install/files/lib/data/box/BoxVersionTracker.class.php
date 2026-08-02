<?php

namespace wcf\data\box;

use wcf\acp\form\BoxEditForm;
use wcf\data\box\content\BoxContent;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IVersionTrackerObject;
use wcf\system\request\LinkHandler;

/**
 * Represents a box with version tracking.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   Box
 * @extends DatabaseObjectDecorator<Box>
 */
class BoxVersionTracker extends DatabaseObjectDecorator implements IVersionTrackerObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Box::class;

    /**
     * list of box content objects
     * @var BoxContent[]
     */
    protected $content = [];

    #[\Override]
    public function getObjectID()
    {
        return $this->getDecoratedObject()->boxID;
    }

    /**
     * Adds an box content object as child.
     *
     * @param BoxContent $content box content object
     * @return void
     */
    public function addContent(BoxContent $content)
    {
        $this->content[] = $content;
    }

    /**
     * Sets the list of box content objects.
     *
     * @param BoxContent[] $content box content objects
     * @return void
     */
    public function setContent(array $content)
    {
        $this->content = $content;
    }

    /**
     * Returns the list of stored box content objects.
     *
     * @return      BoxContent[]    stored box content objects
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
    public function getUserID(): int
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
            BoxEditForm::class,
            ['id' => $this->getDecoratedObject()->boxID]
        );
    }
}
