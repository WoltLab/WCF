<?php

namespace wcf\system\endpoint\controller\core\files;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\file\File;
use wcf\data\file\FileAction;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;

#[DeleteRequest('/core/files/{id}')]
final class DeleteFile implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $file = new File($variables['id']);
        if (!$file->fileID) {
            throw new UserInputException('id');
        }

        if (!$file->canDelete()) {
            throw new PermissionDeniedException();
        }

        // TODO: How do we handle the cleanup of files?
        $fileAction = new FileAction([$file], 'delete');
        $fileAction->executeAction();

        return new JsonResponse([]);
    }
}
