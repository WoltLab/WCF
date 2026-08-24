<?php

namespace wcf\command\unfurl\url;

use wcf\data\file\File;
use wcf\data\file\FileEditor;
use wcf\data\unfurl\url\UnfurlUrl;
use wcf\system\exception\SystemException;
use wcf\system\image\adapter\exception\ImageNotProcessable;
use wcf\system\image\adapter\exception\ImageNotReadable;
use wcf\system\image\ImageHandler;
use wcf\util\FileUtil;

use function wcf\functions\exception\logThrowable;

/**
 * Creates a webp thumbnail for the given image and stores it base64 encoded in a new `.bin` file.
 *
 * Returns `null` if the image could not be processed.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateUnfurlUrlImageFile
{
    public function __construct(
        private readonly string $pathname,
        private readonly string $originalFilename,
    ) {}

    public function __invoke(): ?File
    {
        $imageData = @\getimagesize($this->pathname);
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
            $imageAdapter->loadFile($this->pathname);
            $thumbnail = $imageAdapter->createThumbnail(UnfurlUrl::THUMBNAIL_WIDTH, UnfurlUrl::THUMBNAIL_HEIGHT);
            $imageAdapter->saveImageAs($thumbnail, $webpFile, 'webp', 80);

            // Clean up the thumbnail
            $thumbnail = null;

            $webpContent = \file_get_contents($webpFile);
            if ($webpContent === false) {
                return null;
            }

            // Save the webp file as a base64 encoded binary file
            \file_put_contents($binFile, \base64_encode($webpContent));

            return FileEditor::createFromExistingFile(
                $binFile,
                \pathinfo($this->originalFilename, \PATHINFO_BASENAME) . ".bin",
                'com.woltlab.wcf.unfurl'
            );
        } catch (SystemException | ImageNotReadable $e) {
            return null;
        } catch (ImageNotProcessable $e) {
            logThrowable($e);

            return null;
        } catch (\Throwable $e) {
            logThrowable($e);
            // Ignore any errors trying to save the file unless in debug mode.
            if (\ENABLE_DEBUG_MODE !== 0) {
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
