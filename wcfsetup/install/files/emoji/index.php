<?php

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use GuzzleHttp\Psr7\Header;

\set_error_handler(static function ($severity, $message, $file, $line) {
    if (!(\error_reporting() & $severity)) {
        return;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

require(__DIR__ . '/../lib/system/api/autoload.php');

if (!isset($_GET['l'])) {
    @\http_response_code(404);
    exit;
}
$lang = $_GET['l'];

$emojibase = __DIR__ . "/{$lang}/emojibase/data.json";
$default = __DIR__ . "/{$lang}/cldr/data.json";

if (\file_exists($emojibase)) {
    $location = $emojibase;
} elseif (\file_exists($default)) {
    $location = $default;
} else {
    @\http_response_code(404);
    exit;
}

$fileHash = \sha1_file($location);

$eTag = \sprintf(
    '"%s-%s"',
    $lang,
    \substr($fileHash, 0, 8),
);
@\header('ETag: ' . $eTag);

// Cache emoji for a week
$lifetimeInSeconds = 604_800;
$expiresAt = (new \DateTimeImmutable('@' . \time()))
    ->modify("+{$lifetimeInSeconds} seconds")
    ->format(\DateTimeImmutable::RFC7231);
$maxAge = \sprintf(
    'public, max-age=%d',
    $lifetimeInSeconds ?: 0,
);
@\header('Expires: ' . $expiresAt);
@\header('Cache-Control: ' . $maxAge);

$httpIfNoneMatch = \array_map(
    static fn($tag) => \preg_replace('~^W/~', '', $tag),
    Header::splitList($_REQUEST['HTTP_IF_NONE_MATCH'] ?? '')
);
if (\in_array($eTag, $httpIfNoneMatch, true)) {
    @\http_response_code(304);
    exit;
}

echo \file_get_contents($location);
exit;
