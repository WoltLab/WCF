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
        $href = LinkHandler::getInstance()->getControllerLink(
            $this->controllerClass,
            ['object' => $object]
        );

        if (\is_string($this->languageItem)) {
            $title = WCF::getLanguage()->get($this->languageItem);
        } else {
            $title = ($this->languageItem)($object);
        }

        return \sprintf('<a href="%s">%s</a>', StringUtil::encodeHTML($href), $title);
    }
}
