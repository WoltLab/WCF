<?php

namespace wcf\action;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\file\File;
use wcf\http\ContentDisposition;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\util\FileUtil;

/**
 * Offers a file for download.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class FileDownloadAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    id: positive-int
                }
                EOT,
        );

        $file = new File($parameters['id']);
        if (!$file->fileID) {
            throw new IllegalLinkException();
        }

        $processor = $file->getProcessor();
        if ($processor === null) {
            throw new IllegalLinkException();
        }

        if (!$processor->canDownload($file)) {
            throw new PermissionDeniedException();
        }

        $eTag = \sprintf(
            '"%d-%s"',
            $file->fileID,
            \substr($file->fileHash, 0, 8),
        );

        $httpIfNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
        if ($httpIfNoneMatch === $eTag) {
            return new EmptyResponse(304);
        }

        $processor->trackDownload($file);

        $filename = $file->getPathname();
        $response = new Response(
            new Stream($filename),
        );

        $mimeType = FileUtil::getMimeType($filename);
        $contentDisposition = match ($mimeType) {
            'image/gif',
            'image/jpeg',
            'image/png',
            'image/x-png',
            'application/pdf',
            'image/pjpeg',
            'image/webp' => ContentDisposition::Inline,
            default => ContentDisposition::Attachment,
        };

        // Prevent <script> execution in the context of the community's domain if
        // an attacker somehow bypasses 'content-disposition: attachment' for non-inline
        // MIME-Types. One possibility might be a package extending $inlineMimeTypes
        // in an unsafe fashion.
        //
        // Allow style-src 'unsafe-inline', because otherwise the integrated PDF viewer
        // of Safari will fail to apply its own trusted stylesheet.
        $response = $response
            ->withHeader('content-security-policy', "default-src 'none'; style-src 'unsafe-inline';")
            ->withHeader('x-content-type-options', 'nosniff');

        $lifetimeInSeconds = $processor->getFileCacheDuration($file)->lifetimeInSeconds;
        if ($lifetimeInSeconds !== null) {
            $expiresAt = \sprintf(
                '%s GMT',
                \gmdate('D, d M Y H:i:s', $lifetimeInSeconds)
            );
            $maxAge = \sprintf(
                'max-age=%d, private',
                $lifetimeInSeconds ?: 0,
            );

            $response = $response
                ->withHeader('Expires', $expiresAt)
                ->withHeader('Cache-control', $maxAge);
        }

        return $response
            ->withHeader('content-type', $mimeType)
            ->withHeader(
                'content-disposition',
                $contentDisposition->forFilename($file->filename),
            )
            ->withHeader('ETag', $eTag);
    }
}
