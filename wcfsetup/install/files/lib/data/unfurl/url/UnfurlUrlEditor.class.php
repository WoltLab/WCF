<?php

namespace wcf\data\unfurl\url;

use wcf\data\DatabaseObjectEditor;
use wcf\data\file\File;
use wcf\data\file\FileEditor;
use wcf\system\exception\SystemException;
use wcf\system\image\adapter\exception\ImageNotProcessable;
use wcf\system\image\adapter\exception\ImageNotReadable;
use wcf\system\image\ImageHandler;
use wcf\util\FileUtil;

use wcf\http\error\ExceptionLogger;

/**
 * Provide functions to edit an unfurl url.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
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
     */
    public static function saveUnfurlImage(string $file, string $originalFile): ?File
    {
        $imageData = @\getimagesize($file);
        if ($imageData === false) {
            return null;
        }

        $imageAdapter = ImageHandler::getInstance()->getAdapter();
        if (!$imageAdapter->checkMemoryLimit($imageData[0], $imageData[1], $imageData['mime'])) {
            return null;
        }
        $webpFile = FileUtil::getTemporaryFilename(extension: 'webp');
        $binFile = FileUtil::getTemporaryFilename(extension: 'bin');

        try {
            $imageAdapter->loadFile($file);
            $thumbnail = $imageAdapter->createThumbnail(UnfurlUrl::THUMBNAIL_WIDTH, UnfurlUrl::THUMBNAIL_HEIGHT);
            $imageAdapter->saveImageAs($thumbnail, $webpFile, 'webp', 80);

            // Clean up the thumbnail
            $thumbnail = null;

            // Save the webp file as a base64 encoded binary file
            \file_put_contents($binFile, \base64_encode(\file_get_contents($webpFile)));

            return FileEditor::createFromExistingFile(
                $binFile,
                \pathinfo($originalFile, \PATHINFO_BASENAME) . ".bin",
                'com.woltlab.wcf.unfurl'
            );
        } catch (SystemException | ImageNotReadable $e) {
            return null;
        } catch (ImageNotProcessable $e) {
            ExceptionLogger::log($e);

            return null;
        } catch (\Throwable $e) {
            ExceptionLogger::log($e);
            // Ignore any errors trying to save the file unless in debug mode.
            if (\ENABLE_DEBUG_MODE) {
                throw $e;
            }

            return null;
        } finally {
            // Clean up temporary files
            @\unlink($webpFile);
            @\unlink($binFile);
        }
    }
}
