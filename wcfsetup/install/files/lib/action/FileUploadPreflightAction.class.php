<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\file\temporary\FileTemporary;
use wcf\data\file\temporary\FileTemporaryAction;
use wcf\http\Helper;
use wcf\system\request\LinkHandler;

final class FileUploadPreflightAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: For now we only require the filename and size to be provided.
        $parameters = Helper::mapQueryParameters(
            $request->getParsedBody(),
            <<<'EOT'
                    array {
                        filename: non-empty-string,
                        fileSize: positive-int,
                        fileHash: non-empty-string,
                    }
                    EOT,
        );

        $numberOfChunks = FileTemporary::getNumberOfChunks($parameters['fileSize']);
        if ($numberOfChunks > FileTemporary::MAX_CHUNK_COUNT) {
            // TODO: Reject
        }

        $fileTemporary = $this->createTemporaryFile($parameters, $numberOfChunks);

        $endpoints = [];
        for ($i = 0; $i < $numberOfChunks; $i++) {
            $endpoints[] = LinkHandler::getInstance()->getControllerLink(
                FileUploadAction::class,
                [
                    'identifier' => $fileTemporary->identifier,
                    'sequenceNo' => $i,
                ]
            );
        }

        return new JsonResponse([
            'endpoints' => $endpoints,
        ]);
    }

    private function createTemporaryFile(array $parameters, int $numberOfChunks): FileTemporary
    {
        $identifier = \bin2hex(\random_bytes(20));

        $action = new FileTemporaryAction([], 'create', [
            'data' => [
                'identifier' => $identifier,
                'time' => \TIME_NOW,
                'filename' => $parameters['filename'],
                'fileSize' => $parameters['fileSize'],
                'fileHash' => $parameters['fileHash'],
                'chunks' => \str_repeat('0', $numberOfChunks),
            ],
        ]);

        return $action->executeAction()['returnValues'];
    }
}
