<?php

namespace wcf\util;

/**
 * Represents a string that can be safely output to the user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class HtmlString implements \Stringable
{
    private function __construct(
        private readonly string $value
    ) {}

    public static function fromText(string $value): self
    {
        return new self(
            StringUtil::encodeHTML($value),
        );
    }

    public static function fromSafeHtml(string $value): self
    {
        return new self($value);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
