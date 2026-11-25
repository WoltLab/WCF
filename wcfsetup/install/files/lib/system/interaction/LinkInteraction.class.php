<?php

namespace wcf\system\interaction;

use wcf\data\DatabaseObject;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents an interaction that links to a given controller.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class LinkInteraction extends AbstractInteraction
{
    public function __construct(
        string $identifier,
        protected readonly string $controllerClass,
        protected readonly string|\Closure $languageItem,
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct($identifier, $isAvailableCallback);
    }

    #[\Override]
    public function render(DatabaseObject $object): string
    {
        return \sprintf(
            '<a href="%s">%s</a>',
            StringUtil::encodeHTML($this->getLink($object)),
            $this->getTitle($object)
        );
    }

    /**
     * @since 6.3
     */
    protected function getTitle(DatabaseObject $object): string
    {
        if (\is_string($this->languageItem)) {
            return WCF::getLanguage()->get($this->languageItem);
        }

        return ($this->languageItem)($object);
    }

    protected function getLink(DatabaseObject $object): string
    {
        return LinkHandler::getInstance()->getControllerLink(
            $this->controllerClass,
            ['object' => $object]
        );
    }
}
