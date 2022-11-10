<?php

namespace wcf\system\language\preload;

/**
 * Represents a phrase that should be added
 * to the preload cache.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Language\Preload
 * @since 6.0
 */
final class PreloadPhrase
{
    public readonly string $name;
    public readonly bool $literal;

    public function __construct(string $name, bool $literal)
    {
        $this->name = $name;
        $this->literal = $literal;
    }
}
