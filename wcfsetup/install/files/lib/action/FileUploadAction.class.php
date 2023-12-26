<?php

namespace wcf\action;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

final class FileUploadAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: `sequenceNo` should be of type `non-negative-int`, but requires Valinor 1.7+
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                    array {
                        identifier: non-empty-string,
                        sequenceNo: int,
                    }
                    EOT,
        );

        $sql = "SELECT  *
                FROM    wcf1_file_temporary
                WHERE   identifier = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$parameters['identifier']]);
        $row = $statement->fetchSingleRow();

        if ($row === false) {
            throw new IllegalLinkException();
        }

        // Check if this is a valid sequence no.
        // TODO: The chunk calculation shouldn’t be based on a fixed number.
        $chunkSize = 2_000_000;
        $chunks = (int)\ceil($row['filesize'] / $chunkSize);
        if ($parameters['sequenceNo'] >= $chunks) {
            throw new IllegalLinkException();
        }

        // Check if the actual size matches the expectations.
        if ($parameters['sequenceNo'] === $chunks - 1) {
            // The last chunk is most likely smaller than our chunk size.
            $expectedSize = $row['filesize'] - $chunkSize * ($chunks - 1);
        } else {
            $expectedSize = $chunkSize;
        }

        $chunk = \file_get_contents('php://input');
        $actualSize = \strlen($chunk);

        if ($actualSize !== $expectedSize) {
            throw new IllegalLinkException();
        }

        $folderA = \substr($row['identifier'], 0, 2);
        $folderB = \substr($row['identifier'], 2, 2);

        $tmpPath = \sprintf(
            \WCF_DIR . '_data/private/fileUpload/%s/%s/',
            $folderA,
            $folderB,
        );
        if (!\is_dir($tmpPath)) {
            \mkdir($tmpPath, recursive: true);
        }

        $filename = \sprintf(
            '%s-%d.bin',
            $row['identifier'],
            $parameters['sequenceNo'],
        );

        \file_put_contents($tmpPath . $filename, $chunk);

        // TODO: Dummy response to simulate a successful upload of a chunk.
        return new EmptyResponse();
    }
}
