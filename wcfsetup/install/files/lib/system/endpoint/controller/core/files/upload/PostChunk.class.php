<?php

namespace wcf\system\endpoint\controller\core\files\upload;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use wcf\data\file\FileEditor;
use wcf\data\file\temporary\FileTemporary;
use wcf\data\file\temporary\FileTemporaryEditor;
use wcf\system\endpoint\HydrateFromRequestParameter;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\UserInputException;
use wcf\system\io\File;

#[PostRequest('/core/files/upload/{identifier}/chunk/{sequenceNo:\d+}')]
final class PostChunk implements IController
{
    #[HydrateFromRequestParameter('identifier')]
    public FileTemporary $fileTemporary;

    /**
     * Read data in chunks to avoid hitting the memory limit.
     * See https://stackoverflow.com/a/61997147
     */
    private const FREAD_BUFFER_SIZE = 10 * 1_024 * 1_024;

    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $checksum = $request->getHeaderLine('chunk-checksum-sha256');
        if ($checksum === '' || \str_contains($checksum, ',')) {
            // Reject a missing header of multiple values provided by the client.
            throw new UserInputException('chunk-checksum-sha256');
        }

        $sequenceNo = $variables['sequenceNo'];

        // Check if this is a valid sequence no.
        if ($sequenceNo >= $this->fileTemporary->getChunkCount()) {
            throw new UserInputException('sequenceNo', 'outOfRange');
        }

        // Check if this chunk has already been written.
        if ($this->fileTemporary->hasChunk($sequenceNo)) {
            throw new UserInputException('sequenceNo', 'alreadyExists');
        }

        // Validate the chunk size.
        $chunkSize = $this->fileTemporary->getChunkSize();
        $stream = $request->getBody();
        $receivedSize = $stream->getSize();
        if ($receivedSize !== null && $receivedSize > $chunkSize) {
            throw new UserInputException('payload', 'tooLarge');
        }

        $tmpPath = $this->fileTemporary->getPath();
        if (!\is_dir($tmpPath)) {
            \mkdir($tmpPath, recursive: true);
        }

        $file = new File($tmpPath . $this->fileTemporary->getFilename(), 'cb+');
        $file->lock(\LOCK_EX);
        $file->seek($sequenceNo * $chunkSize);

        // Check if the checksum matches the received data.
        $ctx = \hash_init('sha256');
        $total = 0;
        while (!$stream->eof()) {
            // Write the chunk using a buffer to avoid blowing up the memory limit.
            // See https://stackoverflow.com/a/61997147
            $chunk = $stream->read(self::FREAD_BUFFER_SIZE);
            $total += \strlen($chunk);

            if ($total > $chunkSize) {
                throw new UserInputException('file', 'exceedsFileSize');
            }

            \hash_update($ctx, $chunk);
            $file->write($chunk);
        }
        $file->sync();
        $file->close();

        $result = \hash_final($ctx);

        if ($result !== $checksum) {
            throw new UserInputException('payload', 'checksum');
        }

        // Mark the chunk as written.
        $chunks = $this->fileTemporary->chunks;
        $chunks[$sequenceNo] = '1';
        (new FileTemporaryEditor($this->fileTemporary))->update([
            'chunks' => $chunks,
        ]);

        // Check if we have all chunks.
        if ($chunks === \str_repeat('1', $this->fileTemporary->getChunkCount())) {
            // Check if the final result matches the expected checksum.
            $checksum = \hash_file('sha256', $tmpPath . $this->fileTemporary->getFilename());
            if ($checksum !== $this->fileTemporary->fileHash) {
                throw new UserInputException('file', 'checksum');
            }

            $file = FileEditor::createFromTemporary($this->fileTemporary);

            $context = $this->fileTemporary->getContext();
            (new FileTemporaryEditor($this->fileTemporary))->delete();
            unset($this->fileTemporary);

            $processor = $file->getProcessor();
            $processor?->adopt($file, $context);

            $generateThumbnails = false;
            if ($processor !== null && $file->isImage()) {
                $thumbnailFormats = $processor->getThumbnailFormats();
                if ($thumbnailFormats !== []) {
                    $generateThumbnails = true;
                }
            }

            $data = [];
            if ($processor !== null) {
                $data = $processor->getUploadResponse($file);
            }

            return new JsonResponse([
                'completed' => true,
                'generateThumbnails' => $generateThumbnails,
                'fileID' => $file->fileID,
                'objectTypeID' => $file->objectTypeID,
                'mimeType' => $file->mimeType,
                'link' => $file->getLink(),
                'data' => $data,
            ]);
        }

        return new JsonResponse([
            'completed' => false,
        ]);
    }
}
