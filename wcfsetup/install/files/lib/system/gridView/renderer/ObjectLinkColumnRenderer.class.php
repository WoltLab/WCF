<?php

namespace wcf\system\gridView\renderer;

use BadMethodCallException;
use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\data\ITitledObject;
use wcf\system\cache\runtime\AbstractRuntimeCache;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Provides an abstract implementation for the option to format a column as an object link.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
abstract class ObjectLinkColumnRenderer extends DefaultColumnRenderer implements ILinkColumnRenderer
{
    public function __construct(
        private readonly string $controllerClass = '',
        private readonly array $parameters = [],
        private readonly string $titleLanguageItem = ''
    ) {}

    protected abstract function getRuntimeCache(): AbstractRuntimeCache;

    #[\Override]
    public function render(mixed $value, DatabaseObject $row): string
    {
        if (!$value) {
            return '';
        }

        $object = $this->getRuntimeCache()->getObject($value);
        if ($object === null) {
            return '';
        }

        if (!($object instanceof ITitledObject)) {
            throw new BadMethodCallException(\get_class($object) . " does not implement ITitledObject");
        }

        if ($this->controllerClass) {
            if (!($object instanceof IRouteController)) {
                throw new BadMethodCallException(\get_class($object) . " does not implement IRouteController");
            }

            $href = LinkHandler::getInstance()->getControllerLink(
                $this->controllerClass,
                \array_merge($this->parameters, [
                    'object' => $object,
                ])
            );
        } else if ($object instanceof ILinkableObject) {
            $href = $object->getLink();
        } else {
            throw new BadMethodCallException(\get_class($object) . " does not implement ILinkableObject");
        }

        return '<a href="' . StringUtil::encodeHTML($href) . '"'
            . ($this->titleLanguageItem ? ' title="' . WCF::getLanguage()->get($this->titleLanguageItem) . '"' : '') . '>'
            . StringUtil::encodeHTML($object->getTitle())
            . '</a>';
    }

    #[\Override]
    public function prepare(mixed $value, DatabaseObject $row): void
    {
        if (!$value) {
            return;
        }

        $this->getRuntimeCache()->cacheObjectID($value);
    }
}
