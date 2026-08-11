<?php

namespace wcf\event\acp\dashboard\box;

use wcf\event\IPsr14Event;

/**
 * Requests the collection of PHP extensions for the system info ACP dashboard box.
 *
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class PHPExtensionCollecting implements IPsr14Event
{
    /**
     * @var list<string|string[]>
     */
    private array $extensions = [
        'ctype',
        'dom',
        'exif',
        'fileinfo',
        ['gmp', 'bcmath'],
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
     * If `$extension` is an array, the system checks whether one of the extensions is available.
     *
     * @param string|string[] $extension
     */
    public function register(string|array $extension): void
    {
        if (\in_array($extension, $this->extensions, true)) {
            return;
        }
        $this->extensions[] = $extension;
    }

    /**
     * @return list<string|string[]>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
