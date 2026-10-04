<?php

namespace wcf\system\interaction\bulk;

use wcf\data\DatabaseObject;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a bulk interaction that links to a given controller, passing the
 * ids of the selected objects in the `ids` parameter.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class BulkLinkInteraction extends AbstractBulkInteraction
{
    /**
     * @param array<string, mixed> $parameters additional parameters of the link
     */
    public function __construct(
        string $identifier,
        protected readonly string $controllerClass,
        protected readonly string $languageItem,
        ?\Closure $isAvailableCallback = null,
        protected readonly array $parameters = [],
    ) {
        parent::__construct($identifier, $isAvailableCallback);
    }

    #[\Override]
    public function render(array $objects): string
    {
        $objectIDs = \array_values(\array_map(fn(DatabaseObject $object) => $object->getObjectID(), $objects));

        return \sprintf(
            '<a href="%s">%s</a>',
            StringUtil::encodeHTML(
                LinkHandler::getInstance()->getControllerLink($this->controllerClass, \array_merge(
                    $this->parameters,
                    ['ids' => $objectIDs]
                ))
            ),
            WCF::getLanguage()->get($this->languageItem)
        );
    }
}
