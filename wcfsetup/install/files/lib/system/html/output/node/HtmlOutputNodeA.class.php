<?php

namespace wcf\system\html\output\node;

use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriComparator;
use Psr\Http\Message\UriInterface;
use wcf\system\application\ApplicationHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\request\RouteHandler;
use wcf\util\DOMUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Processes links.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class HtmlOutputNodeA extends AbstractHtmlOutputNode
{
    /**
     * @inheritDoc
     */
    protected $tagName = 'a';

    /**
     * @inheritDoc
     */
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        // Find links that are nested inside other links.
        $nestedLinks = \array_filter(
            $elements,
            static fn (\DOMElement $element) => DOMUtil::hasParent($element, 'a'),
        );

        if ($nestedLinks !== []) {
            $elements = \array_filter(
                $elements,
                static function (\DOMElement $element) use ($nestedLinks) {
                    if (\in_array($element, $nestedLinks, true)) {
                        DOMUtil::removeNode($element, true);
                        return false;
                    }

                    return true;
                }
            );
        }

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            try {
                $href = new Uri($element->getAttribute('href'));
            } catch (MalformedUriException) {
                // If the link href is not a valid URI we drop the entire link.
                DOMUtil::removeNode($element, true);

                continue;
            }

            if ($href->getScheme() !== 'mailto') {
                if (ApplicationHandler::getInstance()->isInternalURL($href->__toString())) {
                    if ($href->getHost() === '') {
                        // `withScheme()` implicitly adds `localhost`
                        // https://www.woltlab.com/community/thread/302070-link-ohne-protokoll-zeigt-auf-localhost/
                        $href = $href->withHost(ApplicationHandler::getInstance()->getDomainName());
                    }

                    $href = $href->withScheme(RouteHandler::secureConnection() ? 'https' : 'http');

                    $element->setAttribute(
                        'href',
                        $href->__toString(),
                    );
                } else {
                    /** @var HtmlOutputNodeProcessor $htmlNodeProcessor */
                    self::markLinkAsExternal($element, $htmlNodeProcessor->getHtmlProcessor()->enableUgc);
                }
            }

            $value = StringUtil::trim($element->textContent);
            if ($value === '') {
                if ($element->childElementCount === 0) {
                    // Discard empty links, these were sometimes created by the
                    // previous editor when editing links.
                    DOMUtil::removeNode($element);

                    continue;
                }
            } else if ($this->isSuspiciousValue($value, $href)) {
                $value = $href->__toString();
            }

            if ($this->outputType === 'text/html' || $this->outputType === 'text/simplified-html') {
                if ($value === $href->__toString()) {
                    while ($element->childNodes->length) {
                        DOMUtil::removeNode($element->childNodes->item(0));
                    }

                    $newValue = $value;
                    if (\mb_strlen($newValue) > 60) {
                        try {
                            // The value returned by `Uri::__toString()` can be malformed.
                            // https://github.com/guzzle/psr7/issues/583
                            $uri = new Uri($newValue);
                        } catch (MalformedUriException) {
                            $uri = clone $href;
                        }

                        $schemeHost = Uri::composeComponents(
                            $uri->getScheme(),
                            $uri->getAuthority(),
                            '',
                            null,
                            null,
                        );
                        $pathQueryFragment = Uri::composeComponents(
                            null,
                            null,
                            $uri->getPath(),
                            $uri->getQuery(),
                            $uri->getFragment(),
                        );
                        if (\mb_strlen($pathQueryFragment) > 35) {
                            $pathQueryFragment = \mb_substr($pathQueryFragment, 0, 15) . StringUtil::HELLIP . \mb_substr($pathQueryFragment, -15);
                        }
                        $newValue = $schemeHost . $pathQueryFragment;
                    }

                    $element->appendChild(
                        $element->ownerDocument->createTextNode($newValue)
                    );
                }
            } elseif ($this->outputType === 'text/plain') {
                if ($value !== $href->__toString()) {
                    $text = $value . ' [URL:' . $href->__toString() . ']';
                } else {
                    $text = $href->__toString();
                }

                $htmlNodeProcessor->replaceElementWithText($element, $text, false);
            }
        }
    }

    /**
     * Returns whether the given link value is suspicious with regard
     * to the actual link target.
     *
     * A value is considered suspicious if it is a cross-origin URI (i.e.
     * if one of host, port or scheme differs).
     *
     * @see \GuzzleHttp\Psr7\UriComparator::isCrossOrigin()
     */
    private function isSuspiciousValue(string $value, UriInterface $href): bool
    {
        $regexMatches = \preg_match(FileUtil::LINK_REGEX, $value, $matches);
        if (!$regexMatches) {
            return false;
        }

        // The match can occur somewhere inside the value, therefore we need to
        // verify that the value contains substantially more than just the link.
        $testValue = StringUtil::trim($value);
        $position = \mb_strpos($testValue, $matches[0]);
        if ($position !== false) {
            $testValue = \mb_substr($testValue, 0, $position) . \mb_substr($testValue, $position + \mb_strlen($matches[0]));
            if ($testValue !== '') {
                // Allow the value if the remaining string contains characters
                // equal or greather than 10% of the length of the URL itself.
                //
                // The motivation behind this is to prevent a bad actor from
                // sneaking in a few padding characters to masquerade the URL
                // which could still look like part of the URL to the untrained
                // eye.
                //
                // The minimum required characters is set to 10 to avoid short
                // URLs being used to bypass this check.
                $threshold = \max(
                    \mb_strlen($matches[0]) * 0.1,
                    10,
                );
                if (\mb_strlen($testValue) >= $threshold) {
                    return false;
                }
            }
        }

        try {
            $value = new Uri($value);
        } catch (MalformedUriException) {
            return false;
        }

        return UriComparator::isCrossOrigin($href, $value);
    }

    /**
     * Marks an element as external.
     *
     * @param \DOMElement $element
     * @param bool $isUgc
     */
    public static function markLinkAsExternal(\DOMElement $element, $isUgc = false)
    {
        $element->setAttribute('class', 'externalURL');

        $rel = 'nofollow';
        if (EXTERNAL_LINK_TARGET_BLANK) {
            $rel .= ' noopener';

            $element->setAttribute('target', '_blank');
        }
        if ($isUgc) {
            $rel .= ' ugc';
        }

        $element->setAttribute('rel', $rel);

        // If the link contains only a single image that is floated to the right,
        // then the external link marker is misaligned. Inheriting the CSS class
        // will cause the link marker to behave properly.
        if ($element->childNodes->length === 1) {
            $child = $element->childNodes->item(0);
            if ($child instanceof \DOMElement && $child->nodeName === 'img') {
                if (
                    \preg_match(
                        '~\b(?P<className>messageFloatObject(?:Left|Right))\b~',
                        $child->getAttribute('class'),
                        $match
                    )
                ) {
                    $element->setAttribute('class', $element->getAttribute('class') . ' ' . $match['className']);
                }
            }
        }
    }
}
