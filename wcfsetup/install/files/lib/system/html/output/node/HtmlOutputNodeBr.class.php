<?php

namespace wcf\system\html\output\node;

use DOMElement;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Unwraps <br> and strips trailing <br>.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class HtmlOutputNodeBr extends AbstractHtmlOutputNode
{
    /**
     * @inheritDoc
     */
    protected $tagName = 'br';

    /**
     * @inheritDoc
     */
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $this->unwrap($element);
            $this->removeTrailingBr($element);
        }
    }

    private function unwrap(DOMElement $br): void
    {
        if ($br->previousSibling || $br->nextSibling) {
            return;
        }

        $parent = $br;
        while (($parent = $parent->parentNode) !== null) {
            switch ($parent->nodeName) {
                case "b":
                case "del":
                case "em":
                case "i":
                case "strong":
                case "sub":
                case "sup":
                case "span":
                case "u":
                    if ($br->previousSibling || $br->nextSibling) {
                        return;
                    }

                    $parent->parentNode->insertBefore($br, $parent);
                    $parent->parentNode->removeChild($parent);
                    $parent = $br;

                    break;

                default:
                    return;
            }
        }
    }

    private function removeTrailingBr(DOMElement $br): void
    {
        if ($br->getAttribute("data-cke-filler") === "true") {
            return;
        }

        $paragraph = DOMUtil::closest($br, "p");
        if ($paragraph === null) {
            return;
        }

        if (!DOMUtil::isLastNode($br, $paragraph)) {
            return;
        }

        if ($paragraph->childNodes->length === 1 && $paragraph->childNodes->item(0) === $br) {
            $paragraph->parentNode->removeChild($paragraph);
        } else {
            $br->remove();
        }
    }
}
