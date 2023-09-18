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
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            try {
                $href = new Uri($element->getAttribute('href'));
            } catch (MalformedUriException) {
                // If the link href is not a valid URI we drop the entire link.
                DOMUtil::removeNode($element, true);

                continue;
            }

            if (ApplicationHandler::getInstance()->isInternalURL($href->__toString())) {
                $href = $href->withScheme(RouteHandler::secureConnection() ? 'https' : 'http');

                $element->setAttribute(
                    'href',
                    $href->__toString(),
                );
            } else {
                /** @var HtmlOutputNodeProcessor $htmlNodeProcessor */
                self::markLinkAsExternal($element, $htmlNodeProcessor->getHtmlProcessor()->enableUgc);
            }

            $value = StringUtil::trim($element->textContent);

            if ($value === '' || $this->isSuspiciousValue($value, $href)) {
                $value = $href->__toString();
            }

            if ($this->outputType === 'text/html' || $this->outputType === 'text/simplified-html') {
                if ($value === $href->__toString()) {
                    while ($element->childNodes->length) {
                        DOMUtil::removeNode($element->childNodes->item(0));
                    }

                    $newValue = $value;
                    if (\mb_strlen($newValue) > 60) {
                        $uri = new Uri($newValue);
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
        if (!\preg_match(FileUtil::LINK_REGEX, $value)) {
            return false;
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
            if ($child->nodeType === \XML_ELEMENT_NODE && $child->nodeName === 'img') {
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
