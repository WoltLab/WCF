<?php

namespace wcf\system\gridView;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\ILinkableObject;
use wcf\system\request\LinkHandler;
use wcf\util\StringUtil;

/**
 * Represents a row link in a grid view that uses a link.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class GridViewRowLink extends AbstractGridViewRowLink
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private readonly string $controllerClass = '',
        private readonly array $parameters = [],
        private readonly string $cssClass = '',
        private readonly bool $isLinkableObject = false,
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct($isAvailableCallback);
    }

    #[\Override]
    public function render(mixed $value, DatabaseObject $row, bool $isPrimaryColumn = false): string
    {
        $href = '';
        if ($this->controllerClass) {
            $href = StringUtil::encodeHTML(LinkHandler::getInstance()->getControllerLink(
                $this->controllerClass,
                \array_merge($this->parameters, ['object' => $row])
            ));
        } else if ($this->isLinkableObject) {
            $href = StringUtil::encodeHTML($this->getLink($row));
        }

        $attributes = [];
        $isButton = true;
        if ($href) {
            $attributes[] = 'href="' . $href . '"';
            $isButton = false;
        }
        $attributes[] = 'class="gridView__rowLink ' . StringUtil::encodeHTML($this->cssClass) . '"';
        $attributes[] = 'tabindex="' . ($isPrimaryColumn ? '0' : '-1') . '"';

        if ($isButton) {
            return '<button type="button" ' . implode(' ', $attributes) . '>'
                . $value
                . '</button>';
        } else {
            return '<a ' . implode(' ', $attributes) . '>'
                . $value
                . '</a>';
        }
    }

    private function getLink(DatabaseObject $object): string
    {
        if ($object instanceof ILinkableObject) {
            return $object->getLink();
        }

        if ($object instanceof DatabaseObjectDecorator) {
            $decoratedObject = $object->getDecoratedObject();
            if ($decoratedObject instanceof ILinkableObject) {
                return $decoratedObject->getLink();
            }
        }

        throw new \BadMethodCallException("GridViewRowLink expects object to be an implementation of ILinkableObject.");
    }
}
