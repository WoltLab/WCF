<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

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

        $chunkSize = $this->getOptimalChunkSize();
        $chunks = (int)\ceil($parameters['fileSize'] / $chunkSize);

        $identifier = $this->createTemporaryFile($parameters);

        $endpoints = [];
        for ($i = 0; $i < $chunks; $i++) {
            $endpoints[] = LinkHandler::getInstance()->getControllerLink(
                FileUploadAction::class,
                [
                    'identifier' => $identifier,
                    'sequenceNo' => $i,
                ]
            );
        }

        return new JsonResponse([
            'endpoints' => $endpoints,
        ]);
    }

    private function createTemporaryFile(array $parameters): string
    {
        $identifier = \bin2hex(\random_bytes(20));

        $sql = "INSERT INTO     wcf1_file_temporary
                                (identifier, time, filename, fileSize, fileHash)
                         VALUES (?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $identifier,
            \TIME_NOW,
            $parameters['filename'],
            $parameters['fileSize'],
            $parameters['fileHash'],
        ]);

        return $identifier;
    }

    // TODO: This is currently duplicated in `FileUploadAction`
    private function getOptimalChunkSize(): int
    {
        $postMaxSize = \ini_parse_quantity(\ini_get('post_max_size'));
        if ($postMaxSize === 0) {
            // Disabling it is fishy, assume a more reasonable limit of 100 MB.
            $postMaxSize = 100 * 1_024 * 1_024;
        }

        return $postMaxSize;
    }
}
