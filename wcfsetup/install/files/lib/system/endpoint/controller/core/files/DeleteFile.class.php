<?php

namespace wcf\system\endpoint\controller\core\files;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\file\File;
use wcf\data\file\FileAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;

#[DeleteRequest('/core/files/{id}')]
final class DeleteFile implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $file = Helper::fetchObjectFromRequestParameter($variables['id'], File::class);
        if (!$file->canDelete()) {
            throw new PermissionDeniedException();
        }

        $fileAction = new FileAction([$file], 'delete');
        $fileAction->executeAction();

        return new JsonResponse([]);
    }
}
