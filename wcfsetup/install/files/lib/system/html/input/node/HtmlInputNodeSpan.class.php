<?php

namespace wcf\system\html\input\node;

use wcf\system\html\node\AbstractHtmlNodeProcessor;

/**
 * Processes `<span>` and sanitizes font sizes.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class HtmlInputNodeSpan extends AbstractHtmlInputNode
{
    /**
     * @inheritDoc
     */
    protected $tagName = 'span';

    /**
     * @inheritDoc
     */
    public function isAllowed(AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            if (!$element->hasAttribute('style')) {
                continue;
            }

            $style = \explode(';', $element->getAttribute('style'));
            for ($i = 0, $length = \count($style); $i < $length; $i++) {
                if (\preg_match('~^\s*font-size\s*:(.+)$~', $style[$i], $matches)) {
                    if (\preg_match('~^\s*(?P<size>\d+)(?P<unit>px|pt)\s*$~', $matches[1], $innerMatches)) {
                        if ($innerMatches['unit'] === 'pt') {
                            $min = 8;
                            $max = 36;
                        } else {
                            $min = 12;
                            $max = 48;
                        }

                        $size = \max($min, $innerMatches['size']);
                        $size = \min($max, $size);

                        // enforce size to be within the boundaries
                        $style[$i] = 'font-size: ' . $size . $innerMatches['unit'];
                    } else {
                        // illegal unit
                        unset($style[$i]);
                    }
                }
            }

            $element->setAttribute('style', \implode(';', $style));
        }
    }
}
