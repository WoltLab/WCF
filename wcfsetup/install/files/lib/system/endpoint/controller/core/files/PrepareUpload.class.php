<?php

namespace wcf\system\endpoint\controller\core\files;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use wcf\data\file\temporary\FileTemporary;
use wcf\data\file\temporary\FileTemporaryAction;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\file\processor\FileProcessor;
use wcf\system\file\processor\FileProcessorPreflightResult;
use wcf\util\JSON;
use WoltLab\WebpExif\Chunk\Exif;

/**
 * Prepares the upload of a file.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[PostRequest('/core/files/upload')]
final class PrepareUpload implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, PostUploadParameters::class);

        $fileProcessor = FileProcessor::getInstance()->getProcessorByName($parameters->objectType);
        if ($fileProcessor === null) {
            throw new UserInputException('objectType', 'unknown');
        }

        try {
            $decodedContext = JSON::decode($parameters->context);
        } catch (SystemException) {
            throw new UserInputException('context', 'invalid');
        }

        if ($parameters->fileSize > FileProcessor::getInstance()->getMaximumFileSize()) {
            throw new UserInputException('fileSize', 'tooLarge');
        }

        // Check if the maximum number of accepted files has already been uploaded.
        if (FileProcessor::getInstance()->hasReachedUploadLimit($fileProcessor, $decodedContext)) {
            throw new UserInputException('preflight', 'tooManyFiles');
        }

        $validationResult = $fileProcessor->acceptUpload($parameters->filename, $parameters->fileSize, $decodedContext);
        if (!$validationResult->ok()) {
            match ($validationResult) {
                FileProcessorPreflightResult::InsufficientPermissions => throw new PermissionDeniedException(),
                FileProcessorPreflightResult::InvalidContext => throw new UserInputException('context', 'invalid'),
                default => throw new UserInputException('preflight', $validationResult->toString()),
            };
        }

        $numberOfChunks = FileTemporary::getNumberOfChunks($parameters->fileSize);
        $fileTemporary = $this->createTemporaryFile($parameters, $numberOfChunks);

        return new JsonResponse([
            'identifier' => $fileTemporary->identifier,
            'numberOfChunks' => $numberOfChunks,
        ]);
    }

    private function createTemporaryFile(PostUploadParameters $parameters, int $numberOfChunks): FileTemporary
    {
        $identifier = \bin2hex(\random_bytes(20));
        $objectType = FileProcessor::getInstance()->getObjectType($parameters->objectType);

        $exifBytes = null;
        if ($parameters->exifData !== null) {
            $exifBytes = \hex2bin($parameters->exifData);
            if ($exifBytes === false) {
                $exifBytes = null;
            }
        }

        $exifData = $this->parseExifData($exifBytes);
        if ($exifData !== null) {
            $exifData = JSON::encode($exifData);
        }

        $action = new FileTemporaryAction([], 'create', [
            'data' => [
                'identifier' => $identifier,
                'time' => \TIME_NOW,
                'filename' => $parameters->filename,
                'fileSize' => $parameters->fileSize,
                'fileHash' => $parameters->fileHash,
                'objectTypeID' => $objectType?->objectTypeID,
                'context' => $parameters->context,
                'chunks' => \str_repeat('0', $numberOfChunks),
                'exifData' => $exifData,
            ],
        ]);

        return $action->executeAction()['returnValues'];
    }

    /**
     * @return array<string, array<string, mixed>>|null
     */
    private function parseExifData(?string $exifData): ?array
    {
        if ($exifData === null) {
            return null;
        }

        $data = Exif::forBytes(0, $exifData)->getParsedExif();
        if ($data === null) {
            return null;
        }

        // Remove the `FILE` and `COMPUTED` section because those contain
        // garbled data anyway and we do not need them in the first place.
        unset($data['FILE'], $data['COMPUTED']);

        // We can also discard the `THUMBNAIL` section because it is a
        // pointless feature and we’re not extracting it either.
        unset($data['THUMBNAIL']);

        if ($data === []) {
            return null;
        }

        return $data;
    }
}

/** @internal */
final class PostUploadParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $filename,

        /** @var positive-int **/
        public readonly int $fileSize,

        /** @var non-empty-string */
        public readonly string $fileHash,

        /** @var non-empty-string */
        public readonly string $objectType,

        /** @var non-empty-string */
        public readonly string $context,

        public readonly ?string $exifData = null,
    ) {}
}
