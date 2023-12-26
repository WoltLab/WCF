<?php

namespace wcf\action;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\io\AtomicWriter;
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

        // Write the chunk using a buffer to avoid blowing up the memory limit.
        // See https://stackoverflow.com/a/61997147
        $file = new AtomicWriter($tmpPath . $filename);
        $bufferSize = 1 * 1024 * 1024;

        $fh = \fopen('php://input', 'rb');
        while (!\feof($fh)) {
            $file->write(\fread($fh, $bufferSize));
        }
        \fclose($fh);

        $file->flush();

        // Check if we have all chunks.
        $data = [];
        for ($i = 0; $i < $chunks; $i++) {
            $filename = \sprintf(
                '%s-%d.bin',
                $row['identifier'],
                $i,
            );

            if (\file_exists($tmpPath . $filename)) {
                $data[] = $tmpPath . $filename;
            }
        }

        if (\count($data) === $chunks) {
            // Concatenate the files by reading only a limited buffer at a time
            // to avoid blowing up the memory limit.
            // See https://stackoverflow.com/a/61997147
            $bufferSize = 1 * 1024 * 1024;

            $newFilename = \sprintf('%s-final.bin', $row['identifier']);
            $file = new AtomicWriter($tmpPath . $newFilename);
            foreach ($data as $fileChunk) {
                $fh = \fopen($fileChunk, 'rb');
                while (!\feof($fh)) {
                    $file->write(\fread($fh, $bufferSize));
                }
                \fclose($fh);
            }

            $file->flush();

            \wcfDebug(
                \memory_get_peak_usage(true),
                \hash_file(
                    'sha256',
                    $tmpPath . $newFilename,
                )
            );
        }

        \wcfDebug(\memory_get_peak_usage(true));

        // TODO: Dummy response to simulate a successful upload of a chunk.
        return new EmptyResponse();
    }
}
