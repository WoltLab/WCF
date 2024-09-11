<?php

namespace wcf\action;

use GuzzleHttp\Psr7\Header;
use GuzzleHttp\Psr7\LimitStream;
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
use wcf\system\Regex;
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

        [$startByte, $endByte] = $this->parseRangeHeader($request, $file->fileSize);

        // check if the range is valid
        if (
            $file->fileSize > 0
            && (
                $startByte < 0
                || $startByte >= $file->fileSize
                || $endByte < $startByte
            )
        ) {
            return $response
                ->withStatus(416)
                ->withHeader('accept-ranges', 'bytes')
                ->withHeader('content-range', "bytes */{$file->fileSize}");
        }

        if ($startByte > 0 || $endByte < $file->fileSize - 1) {
            $response = $response
                ->withStatus(206)
                ->withHeader('content-range', \sprintf('bytes %d-%d/%d', $startByte, $endByte, $file->fileSize));

            $response = $response->withBody(
                new LimitStream($response->getBody(), $endByte - $startByte + 1, $startByte)
            );
        }

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
            $expiresAt = (new \DateTimeImmutable('@' . \TIME_NOW))
                ->modify("+{$lifetimeInSeconds} seconds")
                ->format(\DateTimeImmutable::RFC7231);
            $maxAge = \sprintf(
                'max-age=%d, private',
                $lifetimeInSeconds ?: 0,
            );

            $response = $response
                ->withHeader('expires', $expiresAt)
                ->withHeader('cache-control', $maxAge);
        }

        $eTag = \sprintf(
            '"%d-%s"',
            $file->fileID,
            \substr($file->fileHash, 0, 8),
        );

        $httpIfNoneMatch = \array_map(
            static fn ($tag) => \preg_replace('~^W/~', '', $tag),
            Header::splitList($request->getHeaderLine('if-none-match'))
        );
        if (\in_array($eTag, $httpIfNoneMatch, true)) {
            $emptyResponse = new EmptyResponse(304);
            if ($response->hasHeader('expires')) {
                $emptyResponse = $emptyResponse->withHeader('expires', $response->getHeader('expires'));
            }
            if ($response->hasHeader('cache-control')) {
                $emptyResponse = $emptyResponse->withHeader('cache-control', $response->getHeader('cache-control'));
            }

            return $emptyResponse;
        }

        return $response
            ->withHeader('accept-ranges', 'bytes')
            ->withHeader('content-type', $mimeType)
            ->withHeader(
                'content-disposition',
                $contentDisposition->forFilename($file->filename),
            )
            ->withHeader('etag', $eTag);
    }

    private function parseRangeHeader(ServerRequestInterface $request, int $fileSize): array
    {
        $startByte = 0;
        $endByte = $fileSize - 1;

        if ($request->hasHeader('range')) {
            $regex = new Regex('^bytes=(?:(\d+)-(\d+)?|-(\d+))$');
            if ($regex->match($request->getHeaderLine('range'))) {
                $matches = $regex->getMatches();
                $start = (isset($matches[1]) && $matches[1] !== '' ? \intval($matches[1]) : null);
                $end = (isset($matches[2]) && $matches[2] !== '' ? \intval($matches[2]) : null);
                $last = (isset($matches[3]) && $matches[3] !== '' ? \intval($matches[3]) : null);

                if ($start !== null) {
                    $startByte = $start;
                }

                if ($end !== null) {
                    if ($end <= ($fileSize - 1)) {
                        $endByte = $end;
                    }
                }

                if ($start === null && $end === null && $last !== null) {
                    if ($last <= $fileSize) {
                        // negative value; subtract from filesize
                        $startByte = $fileSize - $last;
                    }
                }
            }
        }

        return [$startByte, $endByte];
    }
}
