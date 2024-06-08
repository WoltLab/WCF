<?php

namespace wcf\system\endpoint\controller\core\files;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use wcf\data\file\File;
use wcf\data\file\thumbnail\FileThumbnailList;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\UserInputException;
use wcf\system\file\processor\FileProcessor;

#[PostRequest('/core/files/{id:\d+}/generatethumbnails')]
final class PostGenerateThumbnails implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $file = new File($variables['id']);
        if (!$file->fileID) {
            throw new UserInputException('id');
        }

        FileProcessor::getInstance()->generateWebpVariant($file);
        FileProcessor::getInstance()->generateThumbnails($file);

        $thumbnails = [];
        foreach ($this->getThumbnails($file) as $thumbnail) {
            $thumbnails[] = [
                'identifier' => $thumbnail->identifier,
                'link' => $thumbnail->getLink(),
            ];
        }

        return new JsonResponse($thumbnails);
    }

    /**
     * @return FileThumbnail[]
     */
    private function getThumbnails(File $file): array
    {
        $thumbnailList = new FileThumbnailList();
        $thumbnailList->getConditionBuilder()->add("fileID = ?", [$file->fileID]);
        $thumbnailList->readObjects();

        return $thumbnailList->getObjects();
    }
}
