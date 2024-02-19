<?php

namespace wcf\system\acp\dashboard\box\event;

use wcf\system\event\IEvent;

/**
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class PHPExtensionCollecting implements IEvent
{
    /**
     * @var string[]
     */
    private array $extensions = [
        'ctype',
        'dom',
        'exif',
        'gmp',
        'intl',
        'libxml',
        'mbstring',
        'openssl',
        'pdo',
        'pdo_mysql',
        'zlib',
    ];

    /**
     * Registers a php extension.
     */
    public function register(string $extension): void
    {
        if (\in_array($extension, $this->extensions)) {
            return;
        }
        $this->extensions[] = $extension;
    }

    /**
     * @return string[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
