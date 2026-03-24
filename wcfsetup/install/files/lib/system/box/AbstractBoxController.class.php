<?php

namespace wcf\system\box;

use wcf\data\box\Box;
use wcf\data\box\BoxAction;
use wcf\system\event\EventHandler;

/**
 * Default implementation for box controllers.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractBoxController implements IBoxController
{
    /**
     * database object of this box
     * @var Box
     */
    protected $box;

    /**
     * box content
     * @var string
     */
    public $content;

    /**
     * supported box positions
     * @var string[]
     */
    protected static $supportedPositions = [];

    /**
     * Creates a new instance of AbstractBoxController.
     */
    public function __construct()
    {
        EventHandler::getInstance()->fireAction($this, '__construct');
    }

    #[\Override]
    public function getContent()
    {
        if ($this->content === null) {
            $this->content = '';

            EventHandler::getInstance()->fireAction($this, 'beforeLoadContent');

            $this->loadContent();

            EventHandler::getInstance()->fireAction($this, 'afterLoadContent');
        }

        return $this->content;
    }

    #[\Override]
    public function hasContent()
    {
        return !empty($this->getContent());
    }

    #[\Override]
    public function getImage()
    {
        return null;
    }

    #[\Override]
    public function getLink(): string
    {
        return '';
    }

    #[\Override]
    public function hasLink()
    {
        return false;
    }

    #[\Override]
    public function getBox()
    {
        return $this->box;
    }

    #[\Override]
    public function setBox(Box $box)
    {
        $this->box = $box;
    }

    #[\Override]
    public function saveAdditionalData()
    {
        // always write additional data to make sure that the additional data of the previous box controller
        // are properly overwritten
        (new BoxAction([$this->box], 'update', [
            'data' => ['additionalData' => \serialize($this->getAdditionalData())],
        ]))->executeAction();
    }

    #[\Override]
    public function getTitle()
    {
        return null;
    }

    /**
     * Returns the additional data of the box.
     *
     * @return mixed[]
     */
    protected function getAdditionalData()
    {
        return [];
    }

    #[\Override]
    public static function getSupportedPositions()
    {
        if (!empty(static::$supportedPositions)) {
            return static::$supportedPositions;
        }

        return Box::$availablePositions;
    }

    /**
     * Loads the content of this box.
     *
     * @return void
     */
    abstract protected function loadContent();
}
