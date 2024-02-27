<?php

namespace wcf\system\spider;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final readonly class Spider
{
    public function __construct(
        public string $identifier,
        public string $name,
        public ?string $url = null,
    ) {
    }
}
