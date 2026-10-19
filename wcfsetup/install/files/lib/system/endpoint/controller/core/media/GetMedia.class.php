<?php

namespace wcf\system\endpoint\controller\core\media;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\media\Media;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Returns the data of the media file with the given ID that is required to
 * insert or select it.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/media/{id:\d+}')]
final class GetMedia implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $media = Helper::fetchObjectFromRequestParameter($variables['id'], Media::class);

        if (
            !WCF::getSession()->hasPermission('admin.content.cms.canManageMedia')
            && !WCF::getSession()->hasPermission('admin.content.cms.canUseMedia')
        ) {
            throw new PermissionDeniedException();
        }

        if (!$media->isAccessible()) {
            throw new PermissionDeniedException();
        }

        $editorLinks = ['original' => $media->getLink()];
        foreach ($media->getEmbeddableThumbnailSizes() as $size) {
            $editorLinks[$size] = $media->getThumbnailLink($size);
        }

        $thumbnailLinks = [];
        foreach (\array_keys(Media::getThumbnailSizes()) as $size) {
            $thumbnailLinks[$size . 'ThumbnailLink'] = $media->isImage !== 0 ? $media->getThumbnailLink($size) : '';
        }

        return new JsonResponse([
            'mediaID' => $media->mediaID,
            'categoryID' => $media->categoryID,
            'title' => $media->getTitle(),
            'altText' => $media->getAltText(),
            'filename' => $media->filename,
            'formattedFilesize' => FileUtil::formatFilesize($media->filesize),
            'isImage' => $media->isImage,
            'link' => $media->getLink(),
            'elementTag' => $media->getElementTag(48),
            'editorLinks' => $editorLinks,
            ...$thumbnailLinks,
        ]);
    }
}
