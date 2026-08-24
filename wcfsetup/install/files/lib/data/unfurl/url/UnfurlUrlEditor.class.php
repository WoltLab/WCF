<?php

namespace wcf\data\unfurl\url;

use wcf\command\unfurl\url\CreateUnfurlUrlImageFile;
use wcf\data\DatabaseObjectEditor;
use wcf\data\file\File;

/**
 * Provide functions to edit an unfurl url.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 * @deprecated  6.3 Use `UnfurlUrlBuilder` instead.
 *
 * @mixin   UnfurlUrl
 * @extends DatabaseObjectEditor<UnfurlUrl>
 */
class UnfurlUrlEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    public static $baseClass = UnfurlUrl::class;

    /**
     * Creates a webp thumbnail for the given file and saves it base64 encoded in a new `.bin` file.
     *
     * @deprecated 6.3 Use `CreateUnfurlUrlImageFile` command instead.
     */
    public static function saveUnfurlImage(string $file, string $originalFile): ?File
    {
        return new CreateUnfurlUrlImageFile($file, $originalFile)();
    }
}
