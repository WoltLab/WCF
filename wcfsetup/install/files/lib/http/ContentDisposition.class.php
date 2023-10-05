<?php

namespace wcf\http;

/**
 * Provides functionality to generate a 'content-disposition' header value.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
enum ContentDisposition
{
    case Inline;
    case Attachment;

    public function forFilename(string $filename): string
    {
        $filename = self::sanitizeFilename($filename);
        $asciiFilename = self::sanitizeFilename(self::getAsciiFilename($filename));

        return \sprintf(
            <<<'EOT'
                %s; filename="%s"; filename*=UTF-8''%s
                EOT,
            $this->getToken(),
            \rawurlencode($asciiFilename),
            \rawurlencode($filename)
        );
    }

    private function getToken(): string
    {
        return match ($this) {
            self::Inline => 'inline',
            self::Attachment => 'attachment',
        };
    }

    /**
     * Returns an ASCII filename for the given filename.
     */
    private static function getAsciiFilename(string $filename): string
    {
        return \transliterator_transliterate('Latin-ASCII', $filename);
    }

    /**
     * Sanitizes the given filename, removing special characters that will
     * cause issues on Windows.
     */
    private static function sanitizeFilename(string $filename): string
    {
        return \str_replace(['<', '>', ':', '"', '/', '\\', '|', '?', '*'], '_', $filename);
    }
}
