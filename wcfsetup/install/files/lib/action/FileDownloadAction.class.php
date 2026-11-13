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
use wcf\event\file\DownloadStarting;
use wcf\http\ContentDisposition;
use wcf\http\Helper;
use wcf\system\event\EventHandler;
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
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    id: positive-int,
                    uploaderToken?: non-empty-string
                }
                EOT,
        );

        $file = new File($parameters['id']);
        if ($file->isNil()) {
            throw new IllegalLinkException();
        }

        $processor = $file->getProcessor();
        if ($processor === null) {
            throw new IllegalLinkException();
        }

        $uploaderToken = $parameters['uploaderToken'] ?? null;
        if ($uploaderToken !== null) {
            $canDownload = $processor->canDownloadWithUploaderToken($file, $uploaderToken);
        } else {
            $canDownload = $processor->canDownload($file);
        }

        if (!$canDownload) {
            throw new PermissionDeniedException();
        }

        $filename = $file->getPathname();
        $response = new Response(
            new Stream($filename),
        );
        $response = $response->withHeader('x-robots-tag', 'noindex');

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

        $eTag = \sprintf(
            '"%d-%s"',
            $file->fileID,
            \substr($file->fileHash, 0, 8),
        );

        $range = $this->parseRangeHeader($request, $file->fileSize);

        // A failed `If-Range` requests the full file. Dates never match, because
        // there is no `last-modified` header to compare them with.
        if ($range !== null && $request->hasHeader('if-range') && $request->getHeaderLine('if-range') !== $eTag) {
            $range = null;
        }

        if ($range === false) {
            return new EmptyResponse(416, [
                'accept-ranges' => 'bytes',
                'content-range' => "bytes */{$file->fileSize}",
                'x-robots-tag' => 'noindex',
            ]);
        }

        // Media players and download managers request the remaining parts of
        // a file through additional range requests, which must not be counted
        // as separate downloads.
        if ($range === null || $range[0] === 0) {
            $processor->trackDownload($file);
            EventHandler::getInstance()->fire(new DownloadStarting($file));
        }

        if ($range !== null) {
            [$startByte, $endByte] = $range;
            $response = $response
                ->withStatus(206)
                ->withHeader('content-range', \sprintf('bytes %d-%d/%d', $startByte, $endByte, $file->fileSize))
                ->withBody(
                    new LimitStream($response->getBody(), $endByte - $startByte + 1, $startByte)
                );
        }

        // Prevent <script> execution in the context of the community's domain if
        // an attacker somehow bypasses 'content-disposition: attachment' for non-inline
        // MIME-Types.
        //
        // Allow style-src 'unsafe-inline', because otherwise the integrated PDF viewer
        // of Safari will fail to apply its own trusted stylesheet.
        $response = $response
            ->withHeader('content-security-policy', "default-src 'none'; style-src 'unsafe-inline';")
            ->withHeader('x-content-type-options', 'nosniff');

        $lifetimeInSeconds = $processor->getFileCacheDuration($file)->lifetimeInSeconds;
        if ($lifetimeInSeconds !== null) {
            $expiresAt = new \DateTimeImmutable('@' . \TIME_NOW)
                ->modify("+{$lifetimeInSeconds} seconds")
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('D, d M Y H:i:s \\G\\M\\T');
            $maxAge = \sprintf(
                'max-age=%d, private',
                $lifetimeInSeconds ?: 0,
            );

            $response = $response
                ->withHeader('expires', $expiresAt)
                ->withHeader('cache-control', $maxAge);
        }

        $httpIfNoneMatch = \array_map(
            static fn($tag) => \preg_replace('~^W/~', '', $tag),
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

    /**
     * Returns the first and last byte of the requested range, `null` if the full
     * file is to be sent, or `false` if the range cannot be satisfied.
     *
     * Headers that request other units, multiple ranges or are malformed are
     * ignored, as permitted by RFC 9110, Section 14.2.
     *
     * @return array{0: int, 1: int}|false|null
     */
    private function parseRangeHeader(ServerRequestInterface $request, int $fileSize): array|false|null
    {
        // An empty file has no bytes that a range could address.
        if (!$request->hasHeader('range') || $fileSize === 0) {
            return null;
        }

        $regex = new Regex('^bytes=(?:(\d+)-(\d*)|-(\d+))$', Regex::CASE_INSENSITIVE);
        if ($regex->match($request->getHeaderLine('range')) === 0) {
            return null;
        }

        $matches = $regex->getMatches();
        if (isset($matches[3])) {
            $suffixLength = (int)$matches[3];
            if ($suffixLength === 0) {
                return false;
            }

            return [\max(0, $fileSize - $suffixLength), $fileSize - 1];
        }

        $firstByte = (int)$matches[1];
        $lastByte = $matches[2] === '' ? null : (int)$matches[2];
        if ($lastByte !== null && $lastByte < $firstByte) {
            return null;
        }

        if ($firstByte >= $fileSize) {
            return false;
        }

        return [$firstByte, \min($lastByte ?? $fileSize - 1, $fileSize - 1)];
    }
}
