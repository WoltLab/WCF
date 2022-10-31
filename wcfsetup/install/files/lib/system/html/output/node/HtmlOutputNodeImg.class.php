<?php

namespace wcf\system\html\output\node;

use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyCache;
use wcf\system\application\ApplicationHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\request\LinkHandler;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\CryptoUtil;
use wcf\util\DOMUtil;
use wcf\util\exception\CryptoException;
use wcf\util\StringUtil;
use wcf\util\Url;

/**
 * Processes images.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
class HtmlOutputNodeImg extends AbstractHtmlOutputNode
{
    /**
     * @inheritDoc
     */
    protected $tagName = 'img';

    /**
     * @inheritDoc
     */
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $class = $element->getAttribute('class');
            if (\preg_match('~\bsmiley\b~', $class)) {
                $code = $element->getAttribute('alt');

                /** @var Smiley $smiley */
                $smiley = SmileyCache::getInstance()->getSmileyByCode($code);
                if ($smiley === null || $this->outputType === 'text/plain') {
                    // output as raw code instead
                    $htmlNodeProcessor->replaceElementWithText($element, ' ' . $code . ' ', false);
                } else {
                    // Ensure that the smiley's HTML is up-to-date.
                    $doc = new \DOMDocument();
                    $doc->loadHTML(\sprintf(
                        '<?xml version="1.0" encoding="UTF-8"?><html><body>%s</body></html>',
                        $smiley->getHtml()
                    ));
                    $smileyNode = $element->ownerDocument->importNode($doc->getElementsByTagName('img')->item(0), true);
                    $element->parentNode->replaceChild($smileyNode, $element);
                }
            } else {
                $src = $element->getAttribute('src');
                if (!$src) {
                    DOMUtil::removeNode($element);
                    continue;
                }

                $class = $element->getAttribute('class');
                if ($class) {
                    $class .= ' ';
                }
                $class .= 'jsResizeImage';
                $element->setAttribute('class', $class);

                if (MODULE_IMAGE_PROXY) {
                    if (!Url::is($src)) {
                        // not a valid URL, discard it
                        DOMUtil::removeNode($element);
                        continue;
                    }

                    $urlComponents = Url::parse($src);
                    if (empty($urlComponents['host'])) {
                        // relative URL, ignore it
                        continue;
                    }

                    if (IMAGE_PROXY_INSECURE_ONLY && $urlComponents['scheme'] === 'https') {
                        // proxy is enabled for insecure connections only
                        if (!IMAGE_ALLOW_EXTERNAL_SOURCE && !$this->isAllowedOrigin($src)) {
                            /** @var HtmlOutputNodeProcessor $htmlNodeProcessor */
                            $this->replaceExternalSource(
                                $element,
                                $src,
                                $htmlNodeProcessor->getHtmlProcessor()->enableUgc
                            );
                        }

                        continue;
                    }

                    if ($this->bypassProxy($urlComponents['host'])) {
                        // check if page was requested over a secure connection
                        // but the link is insecure
                        if (
                            $urlComponents['scheme'] === 'http'
                            && (MESSAGE_FORCE_SECURE_IMAGES || RouteHandler::secureConnection())
                        ) {
                            // rewrite protocol to `https`
                            $element->setAttribute('src', \preg_replace('~^http~', 'https', $src));
                        }

                        continue;
                    }

                    $element->setAttribute('data-valid', 'true');

                    if (!empty($urlComponents['path']) && \preg_match('~\.svg~', \basename($urlComponents['path']))) {
                        // we can't proxy SVG, ignore it
                        continue;
                    }

                    $element->setAttribute('src', $this->getProxyLink($src));

                    $srcset = $element->getAttribute('srcset');
                    if ($srcset) {
                        // simplified regex to check if it appears to be a valid list of sources
                        if (!\preg_match('~^[^\s]+\s+[0-9\.]+[wx](,\s*[^\s]+\s+[0-9\.]+[wx])*~', $srcset)) {
                            $element->removeAttribute('srcset');
                            continue;
                        }

                        $sources = \explode(',', $srcset);
                        $srcset = '';
                        foreach ($sources as $source) {
                            $tmp = \preg_split('~\s+~', StringUtil::trim($source));
                            if (\count($tmp) === 2) {
                                if (!empty($srcset)) {
                                    $srcset .= ', ';
                                }
                                $srcset .= $this->getProxyLink($tmp[0]) . ' ' . $tmp[1];
                            }
                        }

                        $element->setAttribute('srcset', $srcset);
                    }
                } elseif (!IMAGE_ALLOW_EXTERNAL_SOURCE && !$this->isAllowedOrigin($src)) {
                    /** @var HtmlOutputNodeProcessor $htmlNodeProcessor */
                    $this->replaceExternalSource($element, $src, $htmlNodeProcessor->getHtmlProcessor()->enableUgc);
                } elseif (MESSAGE_FORCE_SECURE_IMAGES && Url::parse($src)['scheme'] === 'http') {
                    // rewrite protocol to `https`
                    $element->setAttribute('src', \preg_replace('~^http~', 'https', $src));
                }
            }
        }
    }

    /**
     * Replaces images embedded from external sources that are not handled by the image proxy.
     *
     * @param \DOMElement $element
     * @param string $src
     * @param bool $isUgc
     */
    protected function replaceExternalSource(\DOMElement $element, $src, $isUgc = false)
    {
        $element->parentNode->insertBefore(
            $element->ownerDocument->createTextNode(
                '[' . WCF::getLanguage()->get('wcf.bbcode.image.blocked') . ': '
            ),
            $element
        );

        if (!DOMUtil::hasParent($element, 'a')) {
            $link = $element->ownerDocument->createElement('a');
            $link->setAttribute('href', $src);
            $link->textContent = $src;
            HtmlOutputNodeA::markLinkAsExternal($link, $isUgc);
        } else {
            $link = $element->ownerDocument->createTextNode($src);
        }

        $element->parentNode->insertBefore($link, $element);

        $element->parentNode->insertBefore($element->ownerDocument->createTextNode(']'), $element);

        $element->parentNode->removeChild($element);
    }

    /**
     * @deprecated 5.4 Use Url::getHostnameMatcher().
     */
    protected function getHostMatcher(array $hostnames)
    {
        return Url::getHostnameMatcher($hostnames);
    }

    /**
     * Validates the domain name against the list of own domains
     * and whitelisted ones with wildcard support.
     *
     * @param string $hostname
     * @return      bool
     */
    protected function bypassProxy($hostname)
    {
        static $matcher = null;

        if ($matcher === null) {
            $whitelist = \explode("\n", StringUtil::unifyNewlines(IMAGE_PROXY_HOST_WHITELIST));

            foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
                $host = \mb_strtolower($application->domainName);
                $whitelist[] = $host;
            }

            $matcher = Url::getHostnameMatcher($whitelist);
        }

        return $matcher($hostname);
    }

    /**
     * Returns the link to fetch the image using the image proxy.
     *
     * @param string $link
     * @return  string
     * @since   3.0
     */
    protected function getProxyLink($link)
    {
        try {
            $key = CryptoUtil::createSignedString($link);

            return LinkHandler::getInstance()->getLink('ImageProxy', [
                'key' => $key,
            ]);
        } catch (CryptoException $e) {
            return $link;
        }
    }

    protected function isAllowedOrigin($src)
    {
        static $matcher = null;
        if ($matcher === null) {
            $whitelist = \explode("\n", StringUtil::unifyNewlines(IMAGE_EXTERNAL_SOURCE_WHITELIST));

            foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
                $host = \mb_strtolower($application->domainName);
                $whitelist[] = $host;
            }

            $matcher = Url::getHostnameMatcher($whitelist);
        }

        $host = Url::parse($src)['host'];

        return !$host || $matcher($host);
    }
}
