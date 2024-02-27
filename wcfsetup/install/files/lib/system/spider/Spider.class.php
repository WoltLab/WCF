<?php

namespace wcf\system\spider;

/**
 * Represents a spider object.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class Spider
{
    public readonly string $identifier;

    public function __construct(
        string $identifier,
        public string $name,
        public ?string $url = null,
    ) {
        $this->identifier = \mb_strtolower($identifier);
    }
}
