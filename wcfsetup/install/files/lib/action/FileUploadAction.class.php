<?php

namespace wcf\action;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\file\FileEditor;
use wcf\data\file\temporary\FileTemporary;
use wcf\data\file\temporary\FileTemporaryEditor;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\io\AtomicWriter;
use wcf\system\io\File as IoFile;

final class FileUploadAction implements RequestHandlerInterface
{
    /**
     * Read data in chunks to avoid hitting the memory limit.
     * See https://stackoverflow.com/a/61997147
     */
    private const FREAD_BUFFER_SIZE = 10 * 1_024 * 1_024;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: `sequenceNo` should be of type `non-negative-int`, but requires Valinor 1.7+
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                    array {
                        checksum: non-empty-string,
                        identifier: non-empty-string,
                        sequenceNo: int,
                    }
                    EOT,
        );

        $fileTemporary = new FileTemporary($parameters['identifier']);
        if (!$fileTemporary->identifier) {
            // TODO: Proper error message
            throw new IllegalLinkException();
        }

        // Check if this is a valid sequence no.
        $numberOfChunks = $fileTemporary->getChunkCount();
        if ($parameters['sequenceNo'] >= $fileTemporary->getChunkCount()) {
            // TODO: Proper error message
            throw new IllegalLinkException();
        }

        // Check if the checksum matches the received data.
        $ctx = \hash_init('sha256');
        $stream = $request->getBody();
        while (!$stream->eof()) {
            \hash_update($ctx, $stream->read(self::FREAD_BUFFER_SIZE));
        }
        $result = \hash_final($ctx);
        $stream->rewind();

        if ($result !== $parameters['checksum']) {
            // TODO: Proper error message
            throw new IllegalLinkException();
        }

        $tmpPath = $fileTemporary->getPath();
        if (!\is_dir($tmpPath)) {
            \mkdir($tmpPath, recursive: true);
        }

        // Write the chunk using a buffer to avoid blowing up the memory limit.
        // See https://stackoverflow.com/a/61997147
        $result = new AtomicWriter($tmpPath . $fileTemporary->getChunkFilename($parameters['sequenceNo']));

        while (!$stream->eof()) {
            $result->write($stream->read(self::FREAD_BUFFER_SIZE));
        }

        $result->flush();

        // Check if we have all chunks.
        $data = [];
        for ($i = 0; $i < $numberOfChunks; $i++) {
            $chunkFilename = $fileTemporary->getChunkFilename($i);

            if (\file_exists($tmpPath . $chunkFilename)) {
                $data[] = $tmpPath . $chunkFilename;
            }
        }

        if (\count($data) === $numberOfChunks) {
            // Concatenate the files by reading only a limited buffer at a time
            // to avoid blowing up the memory limit.
            // See https://stackoverflow.com/a/61997147

            $resultFilename = $fileTemporary->getResultFilename();
            $result = new AtomicWriter($tmpPath . $resultFilename);
            foreach ($data as $fileChunk) {
                $source = new IoFile($fileChunk, 'rb');
                try {
                    while (!$source->eof()) {
                        $result->write($source->read(self::FREAD_BUFFER_SIZE));
                    }
                } finally {
                    $source->close();
                }
            }

            $result->flush();

            // Check if the final result matches the expected checksum.
            $checksum = \hash_file('sha256', $tmpPath . $resultFilename);
            if ($checksum !== $fileTemporary->fileHash) {
                // TODO: Proper error message
                throw new IllegalLinkException();
            }

            // Remove the temporary chunks.
            foreach ($data as $fileChunk) {
                \unlink($fileChunk);
            }

            $file = FileEditor::createFromTemporary($fileTemporary);

            (new FileTemporaryEditor($fileTemporary))->delete();

            // TODO: This is just debug code.
            return new JsonResponse([
                'file' => $file->getPath() . $file->getSourceFilename(),
            ]);
        }

        return new EmptyResponse();
    }
}
