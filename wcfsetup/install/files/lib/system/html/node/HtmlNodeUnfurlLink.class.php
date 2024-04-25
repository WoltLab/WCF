<?php

namespace wcf\system\html\node;

use Laminas\Diactoros\Exception\InvalidArgumentException;
use Laminas\Diactoros\Uri;
use wcf\data\unfurl\url\UnfurlUrlAction;
use wcf\util\DOMUtil;
use wcf\util\Url;

/**
 * Helper class to unfurl link objects.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 */
class HtmlNodeUnfurlLink extends HtmlNodePlainLink
{
    public const UNFURL_URL_ID_ATTRIBUTE_NAME = "data-unfurl-url-id";

    /**
     * Marks a link element with the UnfurlUrlID.
     */
    public static function setUnfurl(HtmlNodePlainLink $link): void
    {
        if (!$link->isStandalone()) {
            return;
        }

        try {
            $uri = new Uri($link->href);
        } catch (InvalidArgumentException) {
            return;
        }

        $path = $uri->getPath();
        if ($path !== '') {
            // This is a simplified transformation that will only replace
            // characters that are known to be always invalid in URIs and must
            // be encoded at all times according to RFC 1738.
            $path = \preg_replace_callback(
                '~[^0-9a-zA-Z$-_.+!*\'(),;/?:@=&]~',
                static fn (array $matches) => \rawurlencode($matches[0]),
                $path
            );
            $uri = $uri->withPath($path);

            // The above replacement excludes certain characters from the
            // replacement that are conditionally unsafe.
            if (!Url::is($uri->__toString())) {
                return;
            }
        }

        // Ignore non-standard ports.
        if ($uri->getPort() !== null) {
            return;
        }

        // Ignore non-HTTP schemes.
        if (!\in_array($uri->getScheme(), ['http', 'https'])) {
            return;
        }

        self::removeStyling($link);

        $object = new UnfurlUrlAction([], 'findOrCreate', [
            'data' => [
                'url' => $uri->__toString(),
            ],
        ]);
        $returnValues = $object->executeAction();

        $link->link->setAttribute(self::UNFURL_URL_ID_ATTRIBUTE_NAME, $urlID);
    }

    private static function removeStyling(HtmlNodePlainLink $element): void
    {
        if (!$element->aloneInParagraph) {
            return;
        }
        foreach ($element->topLevelParent->childNodes as $child) {
            DOMUtil::removeNode($child);
        }

        $element->topLevelParent->appendChild($element->link);
    }

    private static function lowercaseHostname(string $url): string
    {
        $uri = new Uri($url);
        $uri = $uri->withHost(\mb_strtolower($uri->getHost()));

        return $uri->__toString();
    }

    private static function findOrCreate(string $url): int
    {
        $object = new UnfurlUrlAction([], 'findOrCreate', [
            'data' => [
                'url' => $url,
            ],
        ]);
        $returnValues = $object->executeAction();

        return $returnValues['returnValues']->urlID;
    }
}
