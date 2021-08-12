<?php

/**
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\RequestOptions;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

\set_error_handler(static function ($severity, $message, $file, $line) {
    if (!(\error_reporting() & $severity)) {
        return;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

require(__DIR__ . '/lib/system/api/autoload.php');

/**
 * Verifies the given $signature is a valid signature for the given $map.
 */
function verifySignature(string $map, string $signature): bool
{
    $publicKey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA45HcF7xOmGe0TSwVwCHE
hFYYxD5SgxchDwQSbX4Wa0XrvhJpo7yKy2QWlfc3CfXZDTLIqPMqAvJEd7xTP9Ny
5OF6cR2NDPjR/ilGN26txhTc2BNzOSXBMZyIwhKHYZZ2JThMT2MRmsVjFeJjbPrX
d8ttf4rEox+ARY/Vaoq+1nx8ZB2B/SwiOS18ESVKRbtKcGtJvenMYxQRx+n7iqy3
ALh9/pSWX5iucJvmW4bBx+RZAn9eTrAjWf5y8Yadc7sMOFWNg2zCgIqqPpsA5ccq
Di1RWwi7vdSudukBovTVfhQ1CkTQ4/r6YxQTYJE2JvyRCMeTEsUHa2Kmwndj/nX3
xQIDAQAB
-----END PUBLIC KEY-----";

    return \openssl_verify(
        $map,
        $signature,
        $publicKey,
        \OPENSSL_ALGO_SHA256
    ) === 1;
}

/**
 * Extracts the map name from the given $query.
 *
 * Returns a string if a map name could be extracted. Returns a class implementing
 * ResponseInterface if the $query is invalid.
 */
function getMapFromQuery(string $query)
{
    try {
        // Step 1: Extract the signature and map name.

        $parts = \explode('/', $query, 2);
        if (\count($parts) !== 2) {
            throw new \UnexpectedValueException('Expected exactly 2 parts within the query string.');
        }

        [$signature, $map] = $parts;

        $signature = Base64UrlSafe::decode($signature, false);

        // Step 2: Verify the signature.
        if (!verifySignature($map, $signature)) {
            throw new \UnexpectedValueException('Failed to verify the signature.');
        }

        // Step 3: Perform a safety check on the map name.
        if (!\preg_match('/^[a-zA-Z0-9]+(\\.[a-zA-Z0-9]+)*\\/[a-f0-9]{64}$/', $map)) {
            // This should be unreachable in real world, because an invalid map is not
            // going to be correctly signed.
            return new TextResponse('Invalid map.', 400);
        }

        // Step 4: Return the extracted map name.
        return $map;
    } catch (\UnexpectedValueException | \RangeException $e) {
        return new TextResponse('Invalid signature.', 400);
    }
}

/**
 * Processes the given request and returns a response to send to the browser.
 */
function handle(ServerRequestInterface $request): ResponseInterface
{
    $mapOrErrorResponse = getMapFromQuery($request->getUri()->getQuery());

    if ($mapOrErrorResponse instanceof ResponseInterface) {
        return $mapOrErrorResponse;
    }

    $map = $mapOrErrorResponse;

    $cacheFilename = \sprintf(
        '%s/tmp/sourcemap_%s.map',
        __DIR__,
        \md5($map)
    );

    if (!\file_exists($cacheFilename)) {
        $cacheDir = \dirname($cacheFilename);
        if (!\is_writable($cacheDir)) {
            throw new \RuntimeException(\sprintf("'%s' is not writable", $cacheDir));
        }

        $remoteRequest = new Request(
            'GET',
            \sprintf(
                'https://assets.woltlab.com/sourcemap/%s.map',
                $map
            ),
            [
                'accept-encoding' => 'gzip',
            ]
        );

        try {
            $client = new Client([
                RequestOptions::PROXY => \PROXY_SERVER_HTTP,
                RequestOptions::TIMEOUT => 5,
                RequestOptions::HEADERS => [
                    'user-agent' => 'WoltLabSuite (SourceMap Proxy)',
                ],
            ]);
            $remoteResponse = $client->send($remoteRequest);
            $target = new Stream(\fopen($cacheFilename, 'w'));

            while (!$remoteResponse->getBody()->eof()) {
                $target->write($remoteResponse->getBody()->read(8192));
            }
        } catch (ClientExceptionInterface $e) {
            \file_put_contents($cacheFilename, '');
        }
    }

    $body = new Stream(\fopen($cacheFilename, 'r'));

    if ($body->getSize() === 0) {
        return new TextResponse('Failed to download the source map.', 503);
    }

    return new Response(
        $body,
        200,
        [
            'content-type' => 'application/json',
            'cache-control' => [
                'immutable',
                'max-age=2592000',
            ],
            'etag' => \sprintf('"%s"', $map),
        ]
    );
}

// Below this point the actual request handling is performed.

try {
    $request = ServerRequestFactory::fromGlobals();

    try {
        require_once(__DIR__ . '/options.inc.php');

        $response = handle($request)
            ->withAddedHeader('cache-control', 'public');
    } catch (\Exception $e) {
        $response = new TextResponse(
            'Internal Server Error',
            500
        );
    }

    $emitter = new SapiStreamEmitter();
    $emitter->emit($response);
} catch (\Exception $e) {
    echo 'Unhandled exception';
}
