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
use wcf\system\io\File as IoFile;
use wcf\system\request\LinkHandler;

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
        if ($parameters['sequenceNo'] >= $fileTemporary->getChunkCount()) {
            // TODO: Proper error message
            throw new IllegalLinkException();
        }

        // Check if this chunk has already been written.
        if ($fileTemporary->hasChunk($parameters['sequenceNo'])) {
            // 409 Conflict
            return new EmptyResponse(409);
        }

        // Validate the chunk size.
        $chunkSize = $fileTemporary->getChunkSize();
        $stream = $request->getBody();
        $receivedSize = $stream->getSize();
        if ($receivedSize !== null && $receivedSize > $chunkSize) {
            // 413 Content Too Large
            return new EmptyResponse(413);
        }

        $tmpPath = $fileTemporary->getPath();
        if (!\is_dir($tmpPath)) {
            \mkdir($tmpPath, recursive: true);
        }

        $file = new IoFile($tmpPath . $fileTemporary->getFilename(), 'cb+');
        $file->lock(\LOCK_EX);
        $file->seek($parameters['sequenceNo'] * $chunkSize);

        // Check if the checksum matches the received data.
        $ctx = \hash_init('sha256');
        $total = 0;
        while (!$stream->eof()) {
            // Write the chunk using a buffer to avoid blowing up the memory limit.
            // See https://stackoverflow.com/a/61997147
            $chunk = $stream->read(self::FREAD_BUFFER_SIZE);
            $total += \strlen($chunk);

            if ($total > $chunkSize) {
                // 413 Content Too Large
                return new EmptyResponse(413);
            }

            \hash_update($ctx, $chunk);
            $file->write($chunk);
        }
        $file->sync();
        $file->close();

        $result = \hash_final($ctx);

        if ($result !== $parameters['checksum']) {
            // TODO: Proper error message
            throw new IllegalLinkException();
        }

        // Mark the chunk as written.
        $chunks = $fileTemporary->chunks;
        $chunks[$parameters['sequenceNo']] = '1';
        (new FileTemporaryEditor($fileTemporary))->update([
            'chunks' => $chunks,
        ]);

        // Check if we have all chunks.
        if ($chunks === \str_repeat('1', $fileTemporary->getChunkCount())) {
            // Check if the final result matches the expected checksum.
            $checksum = \hash_file('sha256', $tmpPath . $fileTemporary->getFilename());
            if ($checksum !== $fileTemporary->fileHash) {
                // TODO: Proper error message
                throw new IllegalLinkException();
            }

            $file = FileEditor::createFromTemporary($fileTemporary);

            $context = $fileTemporary->getContext();
            (new FileTemporaryEditor($fileTemporary))->delete();
            unset($fileTemporary);

            $processor = $file->getProcessor();
            if ($processor === null) {
                // TODO: Mark the file as orphaned.
                \assert($processor !== null);
            }

            $processor->adopt($file, $context);

            $endpointThumbnails = '';
            if ($file->isImage()) {
                $thumbnailFormats = $processor->getThumbnailFormats();
                if ($thumbnailFormats !== []) {
                    // TODO: Endpoint to generate thumbnails.
                    $endpointThumbnails = LinkHandler::getInstance()->getControllerLink(
                        FileGenerateThumbnailsAction::class,
                        ['id' => $file->fileID],
                    );
                }
            }

            // TODO: This is just debug code.
            return new JsonResponse([
                'completed' => true,
                'endpointThumbnails' => $endpointThumbnails,
                'fileID' => $file->fileID,
                'typeName' => $file->typeName,
                'data' => $processor->getUploadResponse($file),
            ]);
        }

        return new JsonResponse([
            'completed' => false,
        ]);
    }
}
